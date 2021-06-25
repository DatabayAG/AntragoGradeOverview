# UIHook Plugin - GradeOverviewCsv

* PHP: [![Minimum PHP Version](https://img.shields.io/badge/Minimum_PHP-7.2.x-blue.svg)](https://php.net/) [![Maximum PHP Version](https://img.shields.io/badge/Maximum_PHP-7.4.x-blue.svg)](https://php.net/)

* ILIAS: [![Minimum ILIAS Version](https://img.shields.io/badge/Minimum_ILIAS-5.4.x-orange.svg)](https://ilias.de/) [![Maximum ILIAS Version](https://img.shields.io/badge/Maximum_ILIAS-7.x-orange.svg)](https://ilias.de/)

---

## Description

Adds a grade overview where grades can be imported using a csv file.

---

## Installation

1. Clone this repository to **Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/GradeOverviewCsv**
2. Install the Composer dependencies  
   ```bash
   cd Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/GradeOverviewCsv
   composer install --no-dev
   ```
   Developers **MUST** omit the `--no-dev` argument.


3. Login to ILIAS with an administrator account (e.g. root)
4. Select **Plugins** in **Extending ILIAS** inside the **Administration** main menu.
5. Search for the **GradeOverviewCsv** plugin in the list of plugin and choose **Install** from the **Actions** drop down.
6. Choose **Activate** from the **Actions** dropdown.

---