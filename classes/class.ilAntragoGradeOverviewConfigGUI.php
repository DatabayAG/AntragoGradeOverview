<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

use ILIAS\DI\Container;
use ILIAS\Plugin\AntragoGradeOverview\Form\GeneralConfigForm;
use ILIAS\Plugin\AntragoGradeOverview\Form\CsvImportForm;
use ILIAS\FileUpload\FileUpload;
use ILIAS\Plugin\AntragoGradeOverview\Model\GradeData;
use ILIAS\Plugin\AntragoGradeOverview\Model\ImportHistory;
use ILIAS\Plugin\AntragoGradeOverview\Repository\ImportHistoryRepository;
use ILIAS\Plugin\AntragoGradeOverview\Repository\GradeDataRepository;
use ILIAS\Plugin\AntragoGradeOverview\Table\ImportHistoryTable;
use ILIAS\Plugin\AntragoGradeOverview\Exception\ValueConvertException;
use ILIAS\Plugin\AntragoGradeOverview\Model\Datasets;
use ILIAS\Plugin\AntragoGradeOverview\Table\GradeDataOverviewTable;
use ILIAS\Plugin\AntragoGradeOverview\Utils\UiUtil;

require_once __DIR__ . '/../vendor/autoload.php';

/**
 * Class ilAntragoGradeOverviewConfigGUI
 * @author            Marvin Beym <mbeym@databay.de>
 * @ilCtrl_Calls      ilAntragoGradeOverviewConfigGUI: ilPropertyFormGUI
 * @ilCtrl_Calls      ilAntragoGradeOverviewConfigGUI: ilExplorerSelectInputGUI
 * @ilCtrl_Calls      ilAntragoGradeOverviewConfigGUI: ilFileSystemGUI
 * @ilCtrl_Calls      ilAntragoGradeOverviewConfigGUI: ilAdministrationGUI
 * @ilCtrl_IsCalledBy ilAntragoGradeOverviewConfigGUI: ilObjComponentSettingsGUI
 */
class ilAntragoGradeOverviewConfigGUI extends ilPluginConfigGUI
{
    protected const AGOP_SETTINGS_TAB = "agop_settings_tab";
    protected const AGOP_GENERAL_SUBTAB = "agop_general_subTab";
    protected const AGOP_CSV_IMPORT_SUBTAB = "agop_csv_import_subTab";
    protected const AGOP_GRADE_DATA_SUBTAB = "agop_csv_grade_data_subTab";
    protected const AGOP_CSV_SEPARATOR = ";";

    protected const ALLOWED_CSV_MIME_TYPES = ["text/csv", "application/vnd.ms-excel"];

    protected ImportHistoryRepository $importHistoryRepo;
    protected GradeDataRepository $gradeDataRepo;
    protected ilObjUser $user;
    protected ilLogger $logger;
    protected FileUpload $upload;
    protected ilAntragoGradeOverviewPlugin $plugin;
    protected ilTabsGUI $tabs;
    protected Container $dic;
    protected ilGlobalPageTemplate $mainTpl;
    protected ilLanguage $lng;
    private ilCtrl $ctrl;
    private UiUtil $uiUtil;

    public function __construct()
    {
        global $DIC;
        $this->dic = $DIC;
        $this->lng = $this->dic->language();
        $this->ctrl = $this->dic->ctrl();
        $this->mainTpl = $this->dic->ui()->mainTemplate();
        $this->tabs = $this->dic->tabs();
        $this->upload = $this->dic->upload();
        $this->logger = $this->dic->logger()->root();
        $this->user = $this->dic->user();
        $this->gradeDataRepo = GradeDataRepository::getInstance($this->dic->database());
        $this->importHistoryRepo = ImportHistoryRepository::getInstance($this->dic->database());
        $this->uiUtil = new UiUtil($this->dic);

        /** @var ilComponentFactory $componentFactory */
        $componentFactory = $DIC['component.factory'];
        $this->plugin = $componentFactory->getPlugin('agop');
        $this->plugin->denyConfigIfPluginNotActive();
    }

    public function generalSettings(): void
    {
        $this->tabs->activateSubTab(self::AGOP_GENERAL_SUBTAB);
        $form = new GeneralConfigForm();
        $this->mainTpl->setContent($form->getHTML());
    }

    public function saveGeneralSettings(): void
    {
        $this->tabs->activateSubTab(self::AGOP_GENERAL_SUBTAB);

        $form = new GeneralConfigForm();
        if ($form->checkInput()) {
            $form->setValuesByPost();
            $showMainMenu = (bool) $form->getInput("showMainMenuItem");

            $this->plugin->settings->set("showMainMenuItem", (string) $showMainMenu);

            $this->uiUtil->sendSuccess($this->plugin->txt("updateSuccessful"), true);
            $this->ctrl->redirectByClass(self::class, $this->getDefaultCommand());
        }

        $form->setValuesByPost();
        $this->mainTpl->setContent($form->getHTML());
    }

    public function gradeDataOverview(): void
    {
        $this->tabs->activateSubTab(self::AGOP_GRADE_DATA_SUBTAB);
        $table = new GradeDataOverviewTable($this);

        try {
            $gradesData = $this->gradeDataRepo->readAll();
        } catch (ValueConvertException $ex) {
            $gradesData = [];
        }

        $table->setData($table->buildTableData($gradesData));

        $this->mainTpl->setContent(
            $table->getHTML()
        );
    }

    private function handleDeleteGradesDataConfirmDialog(array $ids, string $confirmCmd): bool
    {
        if (count($ids) === 0) {
            $this->uiUtil->sendFailure(
                sprintf(
                    $this->plugin->txt("failure_deleting_multi_grade_data"),
                    ""
                ),
                true
            );
            $this->ctrl->redirectByClass(self::class, "gradeDataOverview");
        }

        $confirmed = (bool) $this->dic->http()->request()->getQueryParams()["confirmed"];
        if (!$confirmed) {
            $confirmation = new ilConfirmationGUI();
            $this->ctrl->setParameterByClass(self::class, "confirmed", true);
            $confirmation->setFormAction($this->ctrl->getFormActionByClass(self::class, 'gradeDataOverview'));
            $confirmation->setConfirm($this->lng->txt('confirm'), $confirmCmd);
            $confirmation->setCancel($this->lng->txt('cancel'), 'gradeDataOverview');
            $confirmation->setHeaderText($this->plugin->txt('confirm_delete_grades_data'));

            try {
                foreach ($this->gradeDataRepo->readAll($ids) as $gradeData) {
                    $confirmation->addItem('id[]', (string) $gradeData->getId(), implode(' | ', [
                        $gradeData->getFpIdNr(),
                        $gradeData->getSemester(),
                        $gradeData->getSubjectName(),
                        $gradeData->getTutor(),
                        $gradeData->getDate()->format("d.m.Y"),
                        $gradeData->getGrade(),
                        $gradeData->getEctsPktTn(),
                        $this->plugin->txt($gradeData->isPassed() ? "passed" : "failed"),
                    ]));
                }
            } catch (Exception $ex) {
                $messageString = "";
                foreach ($ids as $index => $id) {
                    if ($index === count($ids) - 1) {
                        $messageString .= $id;
                    } else {
                        $messageString .= "$id, ";
                    }
                }

                $this->uiUtil->sendFailure(
                    sprintf(
                        $this->plugin->txt("failure_deleting_multi_grade_data"),
                        $messageString
                    ),
                    true
                );
                $this->ctrl->redirectByClass(self::class, "gradeDataOverview");
            }
            $this->mainTpl->setContent($confirmation->getHTML());
            return false;
        }
        return true;
    }

    public function deleteSelectedGradesData(): void
    {
        $ids = $this->dic->http()->request()->getParsedBody()["id"];
        $ids = $ids ?: [];

        if (!$this->handleDeleteGradesDataConfirmDialog($ids, "deleteSelectedGradesData")) {
            return;
        }

        $deletedSuccess = [];
        $deletedFailed = [];

        foreach ($ids as $id) {
            if (!$this->gradeDataRepo->delete((int) $id)) {
                $deletedFailed[] = (int) $id;
            } else {
                $deletedSuccess[] = (int) $id;
            }
        }

        if (count($deletedFailed) > 0) {
            $messageString = "";
            foreach ($deletedFailed as $index => $id) {
                if ($index === count($deletedFailed) - 1) {
                    $messageString .= $id;
                } else {
                    $messageString .= "$id, ";
                }
            }

            $this->uiUtil->sendFailure(
                sprintf(
                    $this->plugin->txt("failure_deleting_multi_grade_data"),
                    $messageString
                ),
                true
            );
        }

        if (count($deletedSuccess) > 0) {
            $messageString = "";
            foreach ($deletedSuccess as $index => $id) {
                if ($index === count($deletedSuccess) - 1) {
                    $messageString .= $id;
                } else {
                    $messageString .= "$id, ";
                }
            }

            $this->uiUtil->sendSuccess(
                sprintf(
                    $this->plugin->txt("success_deleting_multi_grade_data"),
                    $messageString
                ),
                true
            );
        }

        $this->ctrl->redirectByClass(self::class, "gradeDataOverview");
    }

    public function deleteGradeData(): void
    {
        $request = $this->dic->http()->request();
        $id = $request->getQueryParams()["id"] ?? $request->getParsedBody()["id"][0];

        if (!$this->handleDeleteGradesDataConfirmDialog($id ? [$id] : [], "deleteGradeData")) {
            return;
        }

        if (!$this->gradeDataRepo->delete((int) $id)) {
            $this->uiUtil->sendFailure(
                sprintf(
                    $this->plugin->txt("failure_deleting_grade_data"),
                    $id
                ),
                true
            );
        } else {
            $this->uiUtil->sendSuccess(
                sprintf(
                    $this->plugin->txt("success_deleting_grade_data"),
                    $id
                ),
                true
            );
        }

        $this->ctrl->redirectByClass(self::class, "gradeDataOverview");
    }

    public function gradesCsvImport(): void
    {
        $this->tabs->activateSubTab(self::AGOP_CSV_IMPORT_SUBTAB);

        $form = new CsvImportForm();
        try {
            $importHistories = $this->importHistoryRepo->readAll();
        } catch (ValueConvertException $ex) {
            $importHistories = [];
        }

        $table = new ImportHistoryTable($this);
        $tableData = $table->buildTableData($importHistories);

        $table->setData($tableData);
        $this->mainTpl->setContent(
            $form->getHTML() .
            $table->getHTML()
        );
    }

    protected function applyFilterImportHistoryTable(): void
    {
        $table = new ImportHistoryTable($this);
        $table->writeFilterToSession();
        $table->resetOffset();
        $this->gradesCsvImport();
    }

    protected function resetFilterImportHistoryTable(): void
    {
        $table = new ImportHistoryTable($this);
        $table->resetOffset();
        $table->resetFilter();
        $this->gradesCsvImport();
    }

    protected function applyFilterGradeDataOverviewTable(): void
    {
        $table = new GradeDataOverviewTable($this);
        $table->writeFilterToSession();
        $table->resetOffset();
        $this->gradeDataOverview();
    }

    protected function resetFilterGradeDataOverviewTable(): void
    {
        $table = new GradeDataOverviewTable($this);
        $table->resetOffset();
        $table->resetFilter();
        $this->gradeDataOverview();
    }

    public function saveGradesCsvImport(): void
    {
        $this->tabs->activateSubTab(self::AGOP_CSV_IMPORT_SUBTAB);
        $form = new CsvImportForm();

        if ($form->checkInput()) {
            $form->setValuesByPost();

            try {
                $hasUploads = $this->upload->hasUploads();
                $hasBeenProcessed = $this->upload->hasBeenProcessed();
                if ($hasUploads && !$hasBeenProcessed) {
                    $this->upload->process();
                } elseif (!$hasUploads) {
                    $this->logger->warning("Error occurred when trying to process uploaded file");
                    $this->uiUtil->sendFailure($this->plugin->txt("fileImportError_upload"), true);
                    $this->ctrl->redirectByClass(self::class, "gradesCsvImport");
                }

                if ($this->upload->hasBeenProcessed()) {
                    $uploadResult = $this->upload->getResults()[$form->getInput("csvFileImport")["tmp_name"]];
                }
            } catch (Exception $ex) {
                $this->logger->warning("Error occurred when trying to process uploaded file. Ex: {$ex->getMessage()}");
                $this->uiUtil->sendFailure($this->plugin->txt("fileImportError_upload"), true);
                $this->ctrl->redirectByClass(self::class, "gradesCsvImport");
            }

            if (!in_array($uploadResult->getMimeType(), self::ALLOWED_CSV_MIME_TYPES)) {
                $this->uiUtil->sendFailure($this->plugin->txt("fileImportError_invalidMimeType"), true);
                $this->ctrl->redirectByClass(self::class, "gradesCsvImport");
            }

            $gradesData = [];

            try {
                $gradesData = $this->convertCsvIntoModelArr($uploadResult->getPath());
            } catch (ValueConvertException $ex) {
                $this->uiUtil->sendFailure($ex->getMessage(), true);
                $this->ctrl->redirectByClass(self::class, "gradesCsvImport");
            }

            try {
                $datasets = new Datasets($gradesData);
            } catch (ValueConvertException $ex) {
                $this->uiUtil->sendFailure($ex->getMessage(), true);
                $this->ctrl->redirectByClass(self::class, "gradesCsvImport");
                exit;
            }

            $importHistory = (new ImportHistory())
                ->setUserId((int) $this->user->getId())
                ->setDatasetsAdded(count($datasets->getNew()))
                ->setDatasetsChanged(count($datasets->getChanged()))
                ->setDatasetsUnchanged(count($datasets->getUnchanged()))
                ->setDate(new DateTime());

            if (!$this->importHistoryRepo->create($importHistory)) {
                $this->logger->warning("Error occurred when trying to save import history");
                $this->uiUtil->sendFailure($this->plugin->txt("fileImportError_importHistory_not_created"), true);
                $this->ctrl->redirectByClass(self::class, "gradesCsvImport");
            }

            if (!$this->gradeDataRepo->import($datasets)) {
                $this->logger->warning("Error occurred when trying to save grades data to database");
                $this->uiUtil->sendFailure($this->plugin->txt("fileImportError_gradeData_not_imported"), true);
                $this->ctrl->redirectByClass(self::class, "gradesCsvImport");
            }

            $this->logger->info(
                sprintf(
                    "CSV Grades Import successful. %s new Rows imported, %s Rows updated, %s Rows unchanged. %s Rows processed from the CSV file.",
                    count($datasets->getNew()),
                    count($datasets->getChanged()),
                    count($datasets->getUnchanged()),
                    $datasets->getTotal()
                )
            );
            $this->uiUtil->sendSuccess(sprintf(
                $this->plugin->txt("fileImportSuccess"),
                count($datasets->getNew()),
                count($datasets->getChanged()),
                count($datasets->getUnchanged()),
                $datasets->getTotal()
            ), true);
            $this->ctrl->redirectByClass(self::class, "gradesCsvImport");
        }

        $this->mainTpl->setContent($form->getHTML());
    }

    public function performCommand(string $cmd): void
    {
        $this->injectTabs();

        $cmd = $cmd === "configure" ? $this->getDefaultCommand() : $cmd;

        if (method_exists($this, $cmd)) {
            $this->{$cmd}();
        } else {
            $this->uiUtil->sendFailure(sprintf($this->plugin->txt("cmdNotFound"), $cmd), true);
            $this->{$this->getDefaultCommand()}();
        }
    }

    protected function injectTabs(): void
    {
        $this->tabs->addTab(
            self::AGOP_SETTINGS_TAB,
            $this->lng->txt("settings"),
            ""
        );

        $this->tabs->setForcePresentationOfSingleTab(true);
        $this->tabs->addSubTab(
            self::AGOP_GENERAL_SUBTAB,
            $this->lng->txt("general_settings"),
            $this->ctrl->getLinkTargetByClass(self::class, "generalSettings")
        );

        $this->tabs->addSubTab(
            self::AGOP_GRADE_DATA_SUBTAB,
            $this->plugin->txt("grade_data_overview"),
            $this->ctrl->getLinkTargetByClass(self::class, "gradeDataOverview")
        );

        $this->tabs->addSubTab(
            self::AGOP_CSV_IMPORT_SUBTAB,
            $this->plugin->txt("grades_csv_import"),
            $this->ctrl->getLinkTargetByClass(self::class, "gradesCsvImport")
        );

        $this->tabs->activateTab(self::AGOP_SETTINGS_TAB);
    }

    /**
     * @throws ValueConvertException|ilCtrlException
     */
    protected function convertCsvIntoModelArr(string $filePath): array
    {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            $this->uiUtil->sendFailure($this->plugin->txt("fileImportError_fileNotAccessible"), true);
            $this->ctrl->redirectByClass(self::class, "gradesCsvImport");
        }

        $fileContents = file_get_contents($filePath);

        switch (mb_detect_encoding($fileContents, ["UTF-8", "ISO-8859-1"], true)) {
            case "UTF-8":
                break;
            case "ISO-8859-1":
                $fileContents = iconv("ISO-8859-1", "UTF-8", $fileContents);
                if (is_bool($fileContents)) {
                    $this->uiUtil->sendFailure($this->plugin->txt("fileImportError_encodingConversionFailed"), true);
                    $this->ctrl->redirectByClass(self::class, "gradesCsvImport");
                }
                break;
            default:
                $this->uiUtil->sendFailure($this->plugin->txt("fileImportError_unsupportedEncoding"), true);
                $this->ctrl->redirectByClass(self::class, "gradesCsvImport");
        }

        $csv = str_getcsv($fileContents, "\n");

        if (!$this->csvPlausibilityCheck($csv)) {
            $this->uiUtil->sendFailure($this->plugin->txt("fileImportError_plausibilityCheck_failed"), true);
            $this->ctrl->redirectByClass(self::class, "gradesCsvImport");
        }

        //Conversion
        $gradesData = [];
        $csvHeaders = [];
        $requiredFields = [
            "TLN_FP_IDNR",
            "PON01_NAME_LANG",
            "PON01_ABSOLVIERTAM"
        ];

        foreach ($csv as $index => $row) {
            /**
             * Remove BOM bytes
             */
            $bom = pack('CCC', 239, 187, 191);
            if (strncmp($row, $bom, 3) === 0) {
                $row = substr($row, 3);
            }

            $row = str_getcsv($row, self::AGOP_CSV_SEPARATOR);

            if ($index === 0) {
                $csvHeaders = $row;
                continue;
            }

            $row = $this->replaceIndexWithHeaderText($row, $csvHeaders);

            foreach ($requiredFields as $field) {
                if ($row[$field] === "") {
                    $this->logger->warning(
                        "Skipping import of row '$index' because no data was found in the field '$field'. This field is required"
                    );
                    continue 2;
                }
            }

            try {
                $dateString = $row["PON01_ABSOLVIERTAM"];
                if (preg_match('/(\d{2}\.\d{2}\.\d{4})/', $row["PON01_ABSOLVIERTAM"])) {
                    $format = "d.m.Y";
                } else {
                    $format = "d.m.y";
                }
                $date = DateTime::createFromFormat($format, $dateString);
                $row["PON01_ABSOLVIERTAM"] = $date->format("d.m.Y");
            } catch (Exception $ex) {
                throw new ValueConvertException();
            }

            $gradesData[] = (new GradeData())
                ->setDataByAnnotation($row, "@csvCol");
        }
        return $gradesData;
    }

    /**
     * @param string[] $csv
     */
    protected function csvPlausibilityCheck(array $csv): bool
    {
        $nFields = 0;
        $csvHeaders = [];
        foreach ($csv as $index => $row) {
            $row = str_getcsv($row, self::AGOP_CSV_SEPARATOR);
            if ($index === 0) {
                $nFields = count($row);
                $csvHeaders = $row;
                continue;
            }

            $row = $this->replaceIndexWithHeaderText($row, $csvHeaders);

            $dateValid = $this->validateDate($row["PON01_ABSOLVIERTAM"]);
            if (!$dateValid || count($row) !== $nFields) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param string[] $row
     * @param string[] $headers
     * @return array<string, string>
     */
    private function replaceIndexWithHeaderText(array $row, array $headers): array
    {
        $newRow = [];
        $csvHeaders = [];

        foreach ($headers as $header) {
            $csvHeaders[] = str_replace(" ", "", $header);
        }

        foreach ($row as $index => $value) {
            $newRow[$csvHeaders[$index]] = $value;
        }
        return $newRow;
    }

    protected function convertFloat(string $floatValue): float
    {
        return (float) str_replace([',', '.'], '.', $floatValue);
    }

    protected function validateDate(string $date): bool
    {
        if ($date === "") {
            return true;
        }

        try {
            new DateTime($date);
            return true;
        } catch (Exception $ex) {
            return false;
        }
    }

    protected function getDefaultCommand(): string
    {
        return "generalSettings";
    }
}
