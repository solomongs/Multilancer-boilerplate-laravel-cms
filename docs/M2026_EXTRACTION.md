# M2026 Static Archive Extraction

The original static-site archive must be extracted and audited before its HTML, CSS, JavaScript, images and media are migrated into the Laravel theme.

## Required file

Place the untouched archive at one of these locations:

```text
/mnt/data/m2026.zip
```

or inside the repository at:

```text
storage/app/imports/m2026.zip
```

Do not commit the original archive when it contains licensed, confidential or unnecessarily large binary assets. Keep its SHA-256 checksum in the generated audit report instead.

## Extract and audit

From the repository root, run:

```bash
python scripts/extract_m2026.py /mnt/data/m2026.zip \
  --output storage/app/m2026-source \
  --report docs/m2026-audit \
  --force
```

For a repository-local archive:

```bash
python scripts/extract_m2026.py storage/app/imports/m2026.zip \
  --output storage/app/m2026-source \
  --report docs/m2026-audit \
  --force
```

## Run the Python tests

```bash
python -m unittest scripts.tests.test_extract_m2026 -v
```

## Generated output

The extractor creates:

```text
storage/app/m2026-source/       Extracted working copy
docs/m2026-audit/README.md      Human-readable audit
docs/m2026-audit/inventory.json Machine-readable inventory
```

The audit includes:

- archive SHA-256 checksum;
- total files and extracted size;
- HTML page titles and paths;
- CSS, JavaScript, image, font, media and document counts;
- local references from HTML and CSS;
- missing local assets/pages;
- external CDN and third-party references;
- duplicate files grouped by SHA-256 hash.

## Migration sequence after extraction

1. Review every HTML page and map its existing URL.
2. Identify shared header, navigation, footer and reusable sections.
3. Copy original frontend assets into the M2026 theme using stable relative paths.
4. Convert the static shell into Blade layout/components.
5. Convert repeated business content into CMS resources and structured page sections.
6. Preserve JavaScript interactions without adding production Node-server requirements.
7. Register changed URLs in the CMS redirect manager.
8. Re-run the extractor after normalization and compare the audit.
9. Run Laravel tests and the production asset build.
10. Commit the audited source migration to `feature/m2026-refactor` and update the draft pull request.

## Security properties

The extractor rejects:

- absolute ZIP paths;
- parent-directory traversal such as `../`;
- symbolic links in the archive;
- extraction targets outside the configured output directory;
- accidental replacement of an existing extraction directory unless `--force` is explicitly supplied.
