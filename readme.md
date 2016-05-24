# XppWeb
Demo: [xpp.baumgartner.io](http://xpp.baumgartner.io)

## Installation
Full guide is available in the [thesis](https://is.muni.cz/th/423763/prif_b/BcThesis.pdf) ("Technical details" chapter).  
XppWeb is built on Laravel framework - documentation for version 5.2 is available [here](https://laravel.com/docs/5.2).

**Requirements**:
* PHP 5.5.9 and higher
* MySQL, PosgreSQL, MSSQL or SQLite database

### Using SQLite DB
1. Use `touch database.sqlite` to create a SQLite database
2. Specify the following config in `.env` file
```
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/database.sqlite
```

### Compiling XPPAUT binaries
* Ubuntu and Mac OS X binaries are compiled in the root directory of this repository.
* Download the source code for the latest version from [Prof. Bard Ermentrout's website](http://www.math.pitt.edu/~bard/xpp/download.html) and compile for your operating system using `make` command (read the provided instuctions).
* Enter the path to the binary under `xpp_path` key in [XPPWeb's config](config/xppweb.php)
