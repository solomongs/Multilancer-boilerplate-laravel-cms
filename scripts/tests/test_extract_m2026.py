from __future__ import annotations

import importlib.util
import json
import tempfile
import unittest
import zipfile
from pathlib import Path


SCRIPT_PATH = Path(__file__).resolve().parents[1] / "extract_m2026.py"
SPEC = importlib.util.spec_from_file_location("extract_m2026", SCRIPT_PATH)
if SPEC is None or SPEC.loader is None:
    raise RuntimeError(f"Unable to load extractor module: {SCRIPT_PATH}")

extract_m2026 = importlib.util.module_from_spec(SPEC)
SPEC.loader.exec_module(extract_m2026)


class ExtractM2026Test(unittest.TestCase):
    def test_extracts_and_reports_static_site(self) -> None:
        with tempfile.TemporaryDirectory() as temporary_directory:
            root = Path(temporary_directory)
            archive = root / "m2026.zip"
            output = root / "source"
            report = root / "audit"

            with zipfile.ZipFile(archive, "w") as package:
                package.writestr(
                    "index.html",
                    """<!doctype html>
<html>
<head>
    <title>M2026 Home</title>
    <link rel="stylesheet" href="assets/site.css">
</head>
<body>
    <img src="assets/logo.svg" alt="Multilancer">
    <script src="assets/site.js"></script>
    <a href="missing.html">Missing page</a>
</body>
</html>
""",
                )
                package.writestr(
                    "assets/site.css",
                    "body { background-image: url('background.webp'); }",
                )
                package.writestr("assets/site.js", "console.log('m2026');")
                package.writestr("assets/logo.svg", "<svg></svg>")
                package.writestr("assets/background.webp", b"RIFFtest")

            extract_m2026.extract_archive(archive, output, force=False)
            files = extract_m2026.build_file_inventory(output)
            pages, html_references = extract_m2026.parse_html_pages(output)
            css_references = extract_m2026.parse_css_references(output)
            payload = extract_m2026.audit_payload(
                archive,
                output,
                files,
                pages,
                html_references + css_references,
            )
            extract_m2026.write_reports(report, payload)

            self.assertEqual(payload["summary"]["total_files"], 5)
            self.assertEqual(payload["summary"]["html_pages"], 1)
            self.assertEqual(payload["summary"]["missing_local_references"], 1)
            self.assertTrue((report / "README.md").is_file())
            self.assertTrue((report / "inventory.json").is_file())

            inventory = json.loads((report / "inventory.json").read_text(encoding="utf-8"))
            self.assertEqual(inventory["pages"][0]["title"], "M2026 Home")
            self.assertEqual(
                inventory["missing_references"][0]["resolved_path"],
                "missing.html",
            )

    def test_rejects_zip_slip_paths(self) -> None:
        with tempfile.TemporaryDirectory() as temporary_directory:
            root = Path(temporary_directory)
            archive = root / "unsafe.zip"

            with zipfile.ZipFile(archive, "w") as package:
                package.writestr("../outside.html", "unsafe")

            with self.assertRaises(ValueError):
                extract_m2026.extract_archive(archive, root / "source", force=False)

    def test_rejects_existing_output_without_force(self) -> None:
        with tempfile.TemporaryDirectory() as temporary_directory:
            root = Path(temporary_directory)
            archive = root / "m2026.zip"
            output = root / "source"
            output.mkdir()

            with zipfile.ZipFile(archive, "w") as package:
                package.writestr("index.html", "<title>M2026</title>")

            with self.assertRaises(FileExistsError):
                extract_m2026.extract_archive(archive, output, force=False)


if __name__ == "__main__":
    unittest.main()
