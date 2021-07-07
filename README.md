# UIHook Plugin - AntragoGradeOverview

* PHP: [![Minimum PHP Version](https://img.shields.io/badge/Minimum_PHP-7.2.x-blue.svg)](https://php.net/) [![Maximum PHP Version](https://img.shields.io/badge/Maximum_PHP-7.4.x-blue.svg)](https://php.net/)

* ILIAS: [![Minimum ILIAS Version](https://img.shields.io/badge/Minimum_ILIAS-5.4.x-orange.svg)](https://ilias.de/) [![Maximum ILIAS Version](https://img.shields.io/badge/Maximum_ILIAS-7.x-orange.svg)](https://ilias.de/)

---
## Table of contents

- [UIHook Plugin - AntragoGradeOverview](#uihook-plugin---antragogradeoverview)
   * [Notes](#notes)
   * [Images](#images)
   * [Description](#description)
   * [Installation](#installation)
   * [Usage](#usage)
   * [Licence](#licence)

---

## Notes

The **CSV** file has to have a specific layout.   
See https://redmine-intern.office.databay.de/issues/13746 (**leistungsnachweisescheine-Bsp.csv**)

Each new upload overrides all currently stored grades (Database table gets cleared)

---

## Images

<details>
<summary>General settings</summary>

![IMAGE](doc/images/general_settings.png)
</details>

<details>
<summary>Grades CSV Import</summary>

![IMAGE](doc/images/grades_csv_import.png)
</details>

<details>
<summary>Grades overview</summary>
   <details>
   <summary>Ilias 5.4</summary>
   
   ![IMAGE](doc/images/grades_overview_ilias54_step-1.png)
   ![IMAGE](doc/images/grades_overview_ilias54_step-2.png)
   ![IMAGE](doc/images/grades_overview_ilias54_step-3.png)
   </details>

   <details>
   <summary>Ilias 6.x and 7.x</summary>
   
   ![IMAGE](doc/images/grades_overview_ilias6and7_step-1.png)
   ![IMAGE](doc/images/grades_overview_ilias6and7_step-2.png)
   </details>
</details>

---

## Description

Adds a grade overview where grades can be imported using a csv file.

---

## Installation

1. Clone this repository to **Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/AntragoGradeOverview**
2. Install the Composer dependencies
   ```bash
   cd Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/AntragoGradeOverview
   composer install --no-dev
   ```
   Developers **MUST** omit the `--no-dev` argument.


3. Login to ILIAS with an administrator account (e.g. root)
4. Select **Plugins** in **Extending ILIAS** inside the **Administration** main menu.
5. Search for the **AntragoGradeOverview** plugin in the list of plugin and choose **Install** from the **Actions** drop
   down.
6. Choose **Activate** from the **Actions** dropdown.

---

## Usage

1. Open the plugin configuration
2. Open the **Grades CSV Import** Tab and upload a .csv file.
3. In the table below a new entry will be added that shows the import history.
4. See [Images](#images) on where to find Grades overview for your account based on the ILIAS version

---

## License

See LICENSE file in this repository.
