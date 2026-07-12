#!/usr/bin/env python3
"""Safely extract and inventory the original M2026 static-site archive.

Usage:
    python scripts/extract_m2026.py /path/to/m2026.zip

Optional:
    python scripts/extract_m2026.py /path/to/m2026.zip \
        --output storage/app/m2026-source \
        --report docs/m2026-audit

The script:
- rejects unsafe ZIP paths and symbolic links;
- extracts the archive into a deterministic directory;
- identifies HTML pages and common frontend assets;
- parses local and external asset references from HTML;
- reports missing local references and duplicate file hashes;
- writes JSON and Markdown audit reports for the Laravel migration.
"""

from __future__ import annotations

import argparse
import hashlib
import json
import mimetypes
import os
import re
import shutil
import stat
import sys
import zipfile
from collections import Counter, defaultdict
from dataclasses import asdict, dataclass
from html.parser import HTMLParser
from pathlib import Path, PurePosixPath
from typing import Iterable
from urllib.parse import unquote, urlsplit


HTML_EXTENSIONS = {".html", ".htm"}
STYLE_EXTENSIONS = {".css", ".scss", ".sass", ".less"}
SCRIPT_EXTENSIONS = {".js", ".mjs", ".cjs", ".ts", ".tsx", ".jsx"}
IMAGE_EXTENSIONS = {
    ".avif",
    ".bmp",
    ".gif",
    ".ico",
    ".jpeg",
    ".jpg",
    ".png",
    ".svg",
    ".webp",
}
FONT_EXTENSIONS = {".eot", ".otf", ".ttf", ".woff", ".woff2"}
MEDIA_EXTENSIONS = {".mp3", ".mp4", ".ogg", ".ogv", ".wav", ".webm"}
DOCUMENT_EXTENSIONS = {".doc", ".docx", ".pdf", ".ppt", ".pptx", ".xls", ".xlsx"}

REFERENCE_ATTRIBUTES = {
    "a": {"href"},
    "audio": {"src"},
    "embed": {"src"},
    "form": {"action"},
    "iframe": {"src"},
    "img": {"src", "srcset"},
    "input": {"src"},
    "link": {"href"},
    "script": {"src"},
    "source": {"src", "srcset"},
    "track": {"src"},
    "video": {"poster", "src"},
}

EXTERNAL_SCHEMES = {"http", "https", "mailto", "tel", "data", "javascript"}
SRCSET_SPLIT_PATTERN = re.compile(r"\s*,\s*")
CSS_URL_PATTERN = re.compile(r"url\(\s*(['\"]?)(.*?)\1\s*\)", re.IGNORECASE)
CSS_IMPORT_PATTERN = re.compile(
    r"@import\s+(?:url\(\s*)?(['\"])(.*?)\1\s*\)?",
    re.IGNORECASE,
)


@dataclass(frozen=True)
class FileRecord:
    path: str
    extension: str
    category: str
    size_bytes: int
    sha256: str
    mime_type: str | None


@dataclass(frozen=True)
class ReferenceRecord:
    source_file: str
    tag: str
    attribute: str
    value: str
    classification: str
    resolved_path: str | None
    exists: bool | None


class HtmlReferenceParser(HTMLParser):
    """Collect asset, navigation and form references from HTML markup."""

    def __init__(self) -> None:
        super().__init__(convert_charrefs=True)
        self.references: list[tuple[str, str, str]] = []
        self.title: str | None = None
        self._reading_title = False
        self._title_parts: list[str] = []

    def handle_starttag(self, tag: str, attrs: list[tuple[str, str | None]]) -> None:
        normalized_tag = tag.lower()
        if normalized_tag == "title":
            self._reading_title = True

        tracked_attributes = REFERENCE_ATTRIBUTES.get(normalized_tag, set())
        for name, value in attrs:
            if value is None or name.lower() not in tracked_attributes:
                continue

            normalized_name = name.lower()
            values = parse_srcset(value) if normalized_name == "srcset" else [value]
            for reference in values:
                reference = reference.strip()
                if reference:
                    self.references.append((normalized_tag, normalized_name, reference))

    def handle_endtag(self, tag: str) -> None:
        if tag.lower() == "title":
            self._reading_title = False
            title = "".join(self._title_parts).strip()
            self.title = title or None

    def handle_data(self, data: str) -> None:
        if self._reading_title:
            self._title_parts.append(data)


def parse_srcset(value: str) -> list[str]:
    references: list[str] = []
    for candidate in SRCSET_SPLIT_PATTERN.split(value.strip()):
        if not candidate:
            continue
        references.append(candidate.split()[0])
    return references


def parse_arguments() -> argparse.Namespace:
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("archive", type=Path, help="Path to m2026.zip")
    parser.add_argument(
        "--output",
        type=Path,
        default=Path("storage/app/m2026-source"),
        help="Extraction directory (default: storage/app/m2026-source)",
    )
    parser.add_argument(
        "--report",
        type=Path,
        default=Path("docs/m2026-audit"),
        help="Report directory (default: docs/m2026-audit)",
    )
    parser.add_argument(
        "--force",
        action="store_true",
        help="Delete an existing extraction directory before extracting",
    )
    return parser.parse_args()


def sha256_file(path: Path) -> str:
    digest = hashlib.sha256()
    with path.open("rb") as handle:
        for block in iter(lambda: handle.read(1024 * 1024), b""):
            digest.update(block)
    return digest.hexdigest()


def category_for(path: Path) -> str:
    extension = path.suffix.lower()
    if extension in HTML_EXTENSIONS:
        return "html"
    if extension in STYLE_EXTENSIONS:
        return "style"
    if extension in SCRIPT_EXTENSIONS:
        return "script"
    if extension in IMAGE_EXTENSIONS:
        return "image"
    if extension in FONT_EXTENSIONS:
        return "font"
    if extension in MEDIA_EXTENSIONS:
        return "media"
    if extension in DOCUMENT_EXTENSIONS:
        return "document"
    return "other"


def is_symbolic_link(info: zipfile.ZipInfo) -> bool:
    mode = info.external_attr >> 16
    return stat.S_ISLNK(mode)


def safe_member_path(member_name: str) -> PurePosixPath:
    normalized = member_name.replace("\\", "/")
    path = PurePosixPath(normalized)

    if path.is_absolute() or ".." in path.parts:
        raise ValueError(f"Unsafe ZIP member path: {member_name}")

    if not path.parts:
        raise ValueError("ZIP contains an empty member path")

    return path


def extract_archive(archive: Path, output: Path, force: bool) -> None:
    if not archive.is_file():
        raise FileNotFoundError(f"Archive not found: {archive}")
    if not zipfile.is_zipfile(archive):
        raise ValueError(f"Not a valid ZIP archive: {archive}")

    if output.exists():
        if not force:
            raise FileExistsError(
                f"Output directory already exists: {output}. Use --force to replace it."
            )
        shutil.rmtree(output)

    output.mkdir(parents=True, exist_ok=True)
    output_root = output.resolve()

    with zipfile.ZipFile(archive) as package:
        for info in package.infolist():
            if is_symbolic_link(info):
                raise ValueError(f"Symbolic links are not allowed in the archive: {info.filename}")

            member_path = safe_member_path(info.filename)
            destination = output.joinpath(*member_path.parts)
            resolved_destination = destination.resolve()

            if output_root != resolved_destination and output_root not in resolved_destination.parents:
                raise ValueError(f"ZIP member escapes extraction directory: {info.filename}")

            if info.is_dir():
                destination.mkdir(parents=True, exist_ok=True)
                continue

            destination.parent.mkdir(parents=True, exist_ok=True)
            with package.open(info) as source, destination.open("wb") as target:
                shutil.copyfileobj(source, target)


def iter_files(root: Path) -> Iterable[Path]:
    return sorted(path for path in root.rglob("*") if path.is_file())


def build_file_inventory(root: Path) -> list[FileRecord]:
    records: list[FileRecord] = []
    for path in iter_files(root):
        relative = path.relative_to(root).as_posix()
        mime_type, _ = mimetypes.guess_type(path.name)
        records.append(
            FileRecord(
                path=relative,
                extension=path.suffix.lower(),
                category=category_for(path),
                size_bytes=path.stat().st_size,
                sha256=sha256_file(path),
                mime_type=mime_type,
            )
        )
    return records


def classify_reference(value: str) -> str:
    stripped = value.strip()
    if not stripped:
        return "empty"
    if stripped.startswith("#"):
        return "fragment"
    if stripped.startswith("//"):
        return "external"

    scheme = urlsplit(stripped).scheme.lower()
    if scheme in EXTERNAL_SCHEMES:
        return "external"
    if scheme:
        return "external"
    return "local"


def resolve_local_reference(source_file: Path, value: str, root: Path) -> tuple[str, bool]:
    parsed = urlsplit(value)
    decoded_path = unquote(parsed.path)

    if decoded_path.startswith("/"):
        candidate = root / decoded_path.lstrip("/")
    else:
        candidate = source_file.parent / decoded_path

    candidate = candidate.resolve()
    root_resolved = root.resolve()
    if root_resolved != candidate and root_resolved not in candidate.parents:
        return decoded_path, False

    if candidate.is_dir():
        for index_name in ("index.html", "index.htm"):
            index_candidate = candidate / index_name
            if index_candidate.exists():
                candidate = index_candidate
                break

    try:
        relative = candidate.relative_to(root_resolved).as_posix()
    except ValueError:
        relative = decoded_path

    return relative, candidate.exists()


def parse_html_pages(root: Path) -> tuple[list[dict[str, object]], list[ReferenceRecord]]:
    pages: list[dict[str, object]] = []
    references: list[ReferenceRecord] = []

    for html_path in sorted(
        path for path in iter_files(root) if path.suffix.lower() in HTML_EXTENSIONS
    ):
        relative = html_path.relative_to(root).as_posix()
        text = html_path.read_text(encoding="utf-8", errors="replace")
        parser = HtmlReferenceParser()
        parser.feed(text)

        page_references: list[ReferenceRecord] = []
        for tag, attribute, value in parser.references:
            classification = classify_reference(value)
            resolved_path: str | None = None
            exists: bool | None = None

            if classification == "local":
                resolved_path, exists = resolve_local_reference(html_path, value, root)

            record = ReferenceRecord(
                source_file=relative,
                tag=tag,
                attribute=attribute,
                value=value,
                classification=classification,
                resolved_path=resolved_path,
                exists=exists,
            )
            page_references.append(record)
            references.append(record)

        pages.append(
            {
                "path": relative,
                "title": parser.title,
                "size_bytes": html_path.stat().st_size,
                "local_reference_count": sum(
                    reference.classification == "local" for reference in page_references
                ),
                "external_reference_count": sum(
                    reference.classification == "external" for reference in page_references
                ),
                "missing_reference_count": sum(
                    reference.classification == "local" and reference.exists is False
                    for reference in page_references
                ),
            }
        )

    return pages, references


def parse_css_references(root: Path) -> list[ReferenceRecord]:
    references: list[ReferenceRecord] = []
    for css_path in sorted(
        path for path in iter_files(root) if path.suffix.lower() in STYLE_EXTENSIONS
    ):
        relative = css_path.relative_to(root).as_posix()
        text = css_path.read_text(encoding="utf-8", errors="replace")
        values = [match[1] for match in CSS_URL_PATTERN.findall(text)]
        values.extend(match[1] for match in CSS_IMPORT_PATTERN.findall(text))

        for value in values:
            classification = classify_reference(value)
            resolved_path: str | None = None
            exists: bool | None = None
            if classification == "local":
                resolved_path, exists = resolve_local_reference(css_path, value, root)

            references.append(
                ReferenceRecord(
                    source_file=relative,
                    tag="css",
                    attribute="url",
                    value=value,
                    classification=classification,
                    resolved_path=resolved_path,
                    exists=exists,
                )
            )

    return references


def duplicate_groups(files: list[FileRecord]) -> list[dict[str, object]]:
    grouped: dict[str, list[FileRecord]] = defaultdict(list)
    for record in files:
        grouped[record.sha256].append(record)

    duplicates = []
    for checksum, records in grouped.items():
        if len(records) < 2:
            continue
        duplicates.append(
            {
                "sha256": checksum,
                "size_bytes": records[0].size_bytes,
                "paths": sorted(record.path for record in records),
            }
        )

    return sorted(duplicates, key=lambda group: (-len(group["paths"]), group["paths"][0]))


def audit_payload(
    archive: Path,
    output: Path,
    files: list[FileRecord],
    pages: list[dict[str, object]],
    references: list[ReferenceRecord],
) -> dict[str, object]:
    category_counts = Counter(record.category for record in files)
    extension_counts = Counter(record.extension or "[no extension]" for record in files)
    missing = [
        reference
        for reference in references
        if reference.classification == "local" and reference.exists is False
    ]
    external = [reference for reference in references if reference.classification == "external"]

    return {
        "archive": str(archive.resolve()),
        "archive_sha256": sha256_file(archive),
        "extraction_directory": str(output.resolve()),
        "summary": {
            "total_files": len(files),
            "total_bytes": sum(record.size_bytes for record in files),
            "html_pages": len(pages),
            "missing_local_references": len(missing),
            "external_references": len(external),
            "duplicate_groups": len(duplicate_groups(files)),
        },
        "category_counts": dict(sorted(category_counts.items())),
        "extension_counts": dict(sorted(extension_counts.items())),
        "pages": pages,
        "files": [asdict(record) for record in files],
        "references": [asdict(record) for record in references],
        "missing_references": [asdict(record) for record in missing],
        "external_references": [asdict(record) for record in external],
        "duplicate_files": duplicate_groups(files),
    }


def markdown_report(payload: dict[str, object]) -> str:
    summary = payload["summary"]
    category_counts = payload["category_counts"]
    pages = payload["pages"]
    missing = payload["missing_references"]
    external = payload["external_references"]
    duplicates = payload["duplicate_files"]

    lines = [
        "# M2026 Static-Site Audit",
        "",
        f"- Archive SHA-256: `{payload['archive_sha256']}`",
        f"- Total files: **{summary['total_files']}**",
        f"- Total extracted size: **{summary['total_bytes']:,} bytes**",
        f"- HTML pages: **{summary['html_pages']}**",
        f"- Missing local references: **{summary['missing_local_references']}**",
        f"- External references: **{summary['external_references']}**",
        f"- Duplicate file groups: **{summary['duplicate_groups']}**",
        "",
        "## File Categories",
        "",
        "| Category | Files |",
        "|---|---:|",
    ]

    for category, count in category_counts.items():
        lines.append(f"| {category} | {count} |")

    lines.extend(
        [
            "",
            "## HTML Pages",
            "",
            "| Page | Title | Local refs | External refs | Missing refs |",
            "|---|---|---:|---:|---:|",
        ]
    )
    for page in pages:
        title = str(page.get("title") or "").replace("|", "\\|")
        lines.append(
            f"| `{page['path']}` | {title} | {page['local_reference_count']} | "
            f"{page['external_reference_count']} | {page['missing_reference_count']} |"
        )

    lines.extend(["", "## Missing Local References", ""])
    if not missing:
        lines.append("No missing local references were detected.")
    else:
        lines.extend(["| Source | Reference | Resolved path |", "|---|---|---|"])
        for reference in missing:
            lines.append(
                f"| `{reference['source_file']}` | `{reference['value']}` | "
                f"`{reference['resolved_path'] or ''}` |"
            )

    lines.extend(["", "## External Dependencies", ""])
    external_values = sorted({str(reference["value"]) for reference in external})
    if not external_values:
        lines.append("No external HTML or CSS references were detected.")
    else:
        for value in external_values:
            lines.append(f"- `{value}`")

    lines.extend(["", "## Duplicate Files", ""])
    if not duplicates:
        lines.append("No duplicate file hashes were detected.")
    else:
        for group in duplicates:
            lines.append(
                f"### {len(group['paths'])} files — {group['size_bytes']:,} bytes each"
            )
            for path in group["paths"]:
                lines.append(f"- `{path}`")
            lines.append("")

    lines.extend(
        [
            "## Migration Notes",
            "",
            "1. Preserve the source archive unchanged; migrate from the extracted working copy.",
            "2. Resolve missing local references before converting templates.",
            "3. Vendor or explicitly document external runtime dependencies.",
            "4. Convert repeated headers, footers and navigation into Blade components.",
            "5. Convert repeated business content into CMS models/resources rather than duplicated markup.",
            "6. Keep original URLs in the redirect registry when route slugs change.",
            "7. Re-run this audit after asset normalization and compare file/reference counts.",
            "",
        ]
    )

    return "\n".join(lines)


def write_reports(report_directory: Path, payload: dict[str, object]) -> None:
    report_directory.mkdir(parents=True, exist_ok=True)
    json_path = report_directory / "inventory.json"
    markdown_path = report_directory / "README.md"

    json_path.write_text(
        json.dumps(payload, indent=2, ensure_ascii=False) + "\n",
        encoding="utf-8",
    )
    markdown_path.write_text(markdown_report(payload), encoding="utf-8")


def main() -> int:
    arguments = parse_arguments()
    archive = arguments.archive.expanduser()
    output = arguments.output.expanduser()
    report = arguments.report.expanduser()

    try:
        extract_archive(archive, output, arguments.force)
        files = build_file_inventory(output)
        pages, html_references = parse_html_pages(output)
        css_references = parse_css_references(output)
        payload = audit_payload(
            archive=archive,
            output=output,
            files=files,
            pages=pages,
            references=html_references + css_references,
        )
        write_reports(report, payload)
    except (FileExistsError, FileNotFoundError, ValueError, zipfile.BadZipFile) as exception:
        print(f"ERROR: {exception}", file=sys.stderr)
        return 1
    except OSError as exception:
        print(f"FILESYSTEM ERROR: {exception}", file=sys.stderr)
        return 1

    summary = payload["summary"]
    print(f"Extracted: {archive} -> {output}")
    print(f"Files: {summary['total_files']}")
    print(f"HTML pages: {summary['html_pages']}")
    print(f"Missing local references: {summary['missing_local_references']}")
    print(f"External references: {summary['external_references']}")
    print(f"Reports: {report / 'README.md'} and {report / 'inventory.json'}")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
