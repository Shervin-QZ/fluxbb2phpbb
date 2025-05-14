# FluxBB to phpBB Migration Guide

## ✨ Purpose
This script was developed to automate the migration process from an existing FluxBB forum to the phpBB platform, while preserving key data such as users, forums, topics, posts, and SEO-friendly IDs.

---

## 📅 Requirements
- PHP 7.4 or higher
- MySQL or MariaDB database
- phpBB installed and configured

---

## 🔧 Installation
1. Clone the repository:
```bash
git clone https://github.com/Shervin-QZ/fluxbb2phpbb.git
```

2. Configure your database access in the `config.php` file.

3. Upload or place the script on a server where it can be executed (CLI or web).

---

## 🚀 Running the Script
Run the main script via terminal or browser:
```bash
php fluxbb_to_phpbb/import.php
```
The script will automatically import the following:
- Users (with special ID remapping logic)
- Forums and categories (while preserving original IDs)
- Topics and posts (with BBCode parsing)
- User signatures
- Last poster info (via SQL JOIN with `fluxbb_posts`)

---

## 🔐 Password Handling
- Supports old SHA1 hashed passwords from FluxBB
- If phpBB validation fails, a fallback mechanism using FluxBB hash validation is used and password is updated accordingly

---

## 📈 Technical Details
- Forum and post IDs are preserved to maintain SEO and existing links
- Special users (admin/guest) are handled separately to avoid ID conflicts
- Orphaned posts are reassigned to the admin user to avoid inconsistency

---

## 📄 Additional Documentation
Extra documents are available in the [`doc/`](../doc/) folder, including:
- `faq.md`
- `import_structure.md`

---

## ⚠ Limitations
- Some phpBB configurations (e.g., homepage widgets, custom layouts) cannot be automatically migrated
- Manual adjustments may be needed after import

---

## 🛠 Additional Technical Notes
- **Admin Account Credentials:** Ensure the phpBB administrator account uses the same username and email as the FluxBB administrator account. The password may differ if necessary.
- **Database Table Prefixes:** The FluxBB and phpBB tables should reside in the same database but use different table prefixes (e.g., `flux_`, `phpbb_`).
- **Cache Clearing:** After the migration is complete, clear the phpBB cache manually or via the ACP to ensure the new content appears correctly.
- **phpBB Extension Structure:** Each phpBB extension must be located inside `ext/vendorname/extensionname/` directories to be properly recognized.

---

## ⛓ Support
One month of support and bug fixes is included after script delivery.

For questions, contact: [Shervin-QZ](https://github.com/Shervin-QZ)
