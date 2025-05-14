# FluxBB to PhpBB Migration Script

This project provides a custom migration script to transfer data from a FluxBB forum to a PhpBB forum.

## 📦 Features
- Imports users, forums, topics, and posts
- Preserves SEO by keeping post and forum IDs
- Handles BBCode translation in posts and signatures
- Supports secure password transition (SHA1 to PhpBB standard)
- Reassigns orphan posts to admin

## 📁 Directory Structure
```
fluxbb2phpbb/
├── fluxbb_to_phpbb/       # Main scripts for migration
│   ├── import.php
│   ├── users_import.php
│   └── ...
├── doc/                   # Documentation and guides (see below)
│   ├── guide.md
│   └── faq.md
├── sample_data/           # (Optional) Sample SQL dumps for testing
│   └── fluxbb_sample.sql
├── README.md              # Project documentation (this file)
├── LICENSE                # License info (MIT recommended)
└── TODO.md                # List of pending features and notes
```

## 🚀 Usage
### Requirements:
- PHP 7.4+
- MySQL/MariaDB
- Access to both FluxBB and PhpBB databases

### How to run:
1. Configure your DB credentials in `config.php`
2. Run the script:
```bash
php fluxbb_to_phpbb/import.php
```

### Notes:
- Make sure PhpBB is already installed.
- Backup your databases before running the script.

## 📄 Documentation
Please refer to the [`doc/guide.md`](doc/guide.md) for detailed migration steps and customizations.

## 🧑‍💻 Author
Developed by [Shervin-QZ](https://github.com/Shervin-QZ)

## 🪪 License
This project is licensed under the MIT License. See [LICENSE](LICENSE) for details.
