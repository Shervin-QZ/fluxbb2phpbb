# ❓ FAQ - FluxBB to phpBB Migration

## 1. Why are users with ID < 2 not imported?
User ID 1 and 2 are reserved in both FluxBB and phpBB for admin and guest accounts. To maintain compatibility:
- Admin user (ID 1) and Guest user (ID 2) are handled with special mapping.
- phpBB admin account should use the same username and email as the FluxBB admin.

---

## 2. Can I import both databases from different servers?
No. Both FluxBB and phpBB databases should be located in the same database instance, distinguished by table prefixes (e.g., `flux_`, `phpbb_`).

---

## 3. Are passwords preserved?
Yes, with fallback. Passwords hashed using SHA1 in FluxBB are:
- Attempted to validate using phpBB's internal methods.
- If that fails, validated with a FluxBB-compatible check and updated to a phpBB format.

---

## 4. What happens if a post has no user?
Orphaned posts (with no matching user) are reassigned to the admin account to prevent data loss.

---

## 5. Why aren’t forums or categories visible after migration?
Make sure to:
- Clear the phpBB cache after migration.
- Review forum permissions and visibility settings manually.

---

## 6. Why isn’t BBCode rendered correctly?
The script parses most BBCode, but:
- Custom or malformed tags may not convert properly.
- Manual review of imported content is recommended.

---

## 7. Is the import script secure?
Yes. All SQL queries use `sprintf()` and safe bindings to prevent SQL injection.

---

## 8. How should phpBB extensions be organized?
Each extension must be placed under:
```
ext/
  └── vendorname/
       └── extensionname/
```
This structure is required for phpBB to detect and enable extensions properly.

---

## 9. What should I do after import?
- Log in as admin and review imported data.
- Clear phpBB cache (ACP > General > Purge Cache).
- Rebuild search index if necessary.

---

## 10. Where can I find help or report a bug?
Support is included for 1 month. You can contact the developer or report issues via:
[https://github.com/Shervin-QZ/fluxbb2phpbb](https://github.com/Shervin-QZ/fluxbb2phpbb)
