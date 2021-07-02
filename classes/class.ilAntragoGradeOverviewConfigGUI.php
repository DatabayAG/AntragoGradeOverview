<?php

declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\DI\Container;
use ILIAS\Plugin\AntragoGradeOverview\Form\GeneralConfigForm;
use ILIAS\Plugin\AntragoGradeOverview\Form\CsvImportForm;
use ILIAS\FileUpload\FileUpload;
use ILIAS\Plugin\AntragoGradeOverview\Model\GradeData;
use ILIAS\Plugin\AntragoGradeOverview\Model\ImportHistory;
use ILIAS\Plugin\AntragoGradeOverview\Repository\ImportHistoryRepository;
use ILIAS\Plugin\AntragoGradeOverview\Repository\GradeDataRepository;
use ILIAS\Plugin\AntragoGradeOverview\Table\ImportHistoryTable;

require_once __DIR__ . '/../vendor/autoload.php';

/**
 * Class ilAntragoGradeOverviewConfigGUI
 * @author  Marvin Beym <mbeym@databay.de>
 */
class ilAntragoGradeOverviewConfigGUI extends ilPluginConfigGUI
{
    protected const AGOP_SETTINGS_TAB = "agop_settings_tab";
    protected const AGOP_GENERAL_SUBTAB = "agop_general_subTab";
    protected const AGOP_CSV_IMPORT_SUBTAB = "agop_csv_import_subTab";
    protected const AGOP_CSV_SEPARATOR = ";";
    /**
     * @var ImportHistoryRepository
     */
    protected $importHistoryRepo;
    /**
     * @var GradeDataRepository
     */
    protected $gradeDataRepo;
    /**
     * @var ilObjUser
     */
    protected $user;
    /**
     * @var ilLogger
     */
    protected $logger;
    /**
     * @var FileUpload
     */
    protected $upload;
    /**
     * @var ilAntragoGradeOverviewPlugin
     */
    protected $plugin;
    /**
     * @var ilTabsGUI
     */
    protected $tabs;
    /**
     * @var Container
     */
    protected $dic;
    /**
     * @var ilTemplate
     */
    protected $mainTpl;
    /**
     * @var ilLanguage
     */
    protected $lng;
    /**
     * @var ilCtrl
     */
    private $ctrl;

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
        $this->plugin = ilAntragoGradeOverviewPlugin::getInstance();
    }

    /**
     * Show the general settings form/tab
     */
    public function generalSettings()
    {
        $this->tabs->activateSubTab(self::AGOP_GENERAL_SUBTAB);
        $form = new GeneralConfigForm();
        $this->mainTpl->setContent($form->getHTML());
    }

    /**
     * Saves the general settings form
     */
    public function saveGeneralSettings()
    {
        $this->tabs->activateSubTab(self::AGOP_GENERAL_SUBTAB);

        $form = new GeneralConfigForm();
        if ($form->checkInput()) {
            $form->setValuesByPost();
            $gradePassedThreshold = $form->getInput("gradePassedThreshold");
            $showMainMenu = (bool) $form->getInput("showMainMenuItem");

            $this->plugin->settings->set("gradePassedThreshold", $gradePassedThreshold);
            $this->plugin->settings->set("showMainMenuItem", $showMainMenu);

            ilUtil::sendSuccess($this->plugin->txt("updateSuccessful"), true);
            $this->ctrl->redirectByClass(self::class, $this->getDefaultCommand());
        }
        $this->mainTpl->setContent($form->getHTML());
    }

    /**
     * Shows the grades csv import form/tab
     * @throws Exception
     */
    public function gradesCsvImport()
    {
        $this->tabs->activateSubTab(self::AGOP_CSV_IMPORT_SUBTAB);

        $form = new CsvImportForm();
        $importHistories = $this->importHistoryRepo->readAll();

        $table = new ImportHistoryTable($this);
        $tableData = $table->buildTableData($importHistories);

        $paginationData = $this->setupPagination(10, count($tableData));

        $paginatedTableData = array_slice($tableData, $paginationData["start"], $paginationData["stop"]);
        $table->setData($paginatedTableData);

        $this->mainTpl->setContent(
            $form->getHTML() .
            $table->getHTML() .
            $paginationData["html"]
        );
    }

    /**
     * Creates the pagination html string
     * Returns an array with the 'html' and 'currentPage' fields
     * @param int $elementsPerPage
     * @param int $totalNumberOfElements
     * @return array
     */
    protected function setupPagination(int $elementsPerPage, int $totalNumberOfElements) : array
    {
        $factory = $this->dic->ui()->factory();
        $renderer = $this->dic->ui()->renderer();
        $url = $this->dic->http()->request()->getRequestTarget();

        $parameterName = 'page';
        $query = $this->dic->http()->request()->getQueryParams();
        if (isset($query[$parameterName])) {
            $currentPage = (int) $query[$parameterName];
        } else {
            $currentPage = 0;
        }

        $pagination = $factory->viewControl()->pagination()
                              ->withTargetURL($url, $parameterName)
                              ->withTotalEntries($totalNumberOfElements)
                              ->withPageSize($elementsPerPage);

        $maxPage = $pagination->getNumberOfPages() - 1;
        if ($currentPage >= $maxPage) {
            $currentPage = $maxPage;
        }
        if ($currentPage <= 0) {
            $currentPage = 0;
        }

        $pagination = $pagination->withCurrentPage($currentPage);

        $start = $pagination->getPageSize() * $currentPage;
        $stop = $pagination->getPageSize();

        return [
            "html" => $renderer->render($pagination),
            "start" => $start,
            "currentPage" => $currentPage,
            "stop" => $stop
        ];
    }

    /**
     * Applies the filter of the csv import history table
     * @throws Exception
     */
    protected function applyFilter()
    {
        $table = new ImportHistoryTable($this);
        $table->writeFilterToSession();
        $table->resetOffset();
        $this->gradesCsvImport();
    }

    /**
     * Resets the filter of the csv import history table
     * @throws Exception
     */
    protected function resetFilter()
    {
        $table = new ImportHistoryTable($this);
        $table->resetOffset();
        $table->resetFilter();
        $this->gradesCsvImport();
    }

    /**
     * Processes the uploaded csv file
     */
    public function saveGradesCsvImport()
    {
        $this->tabs->activateSubTab(self::AGOP_CSV_IMPORT_SUBTAB);
        $form = new CsvImportForm();

        if ($form->checkInput()) {
            $form->setValuesByPost();

            try {
                if ($this->upload->hasUploads() && !$this->upload->hasBeenProcessed()) {
                    $this->upload->process();
                } elseif (!$this->upload->hasUploads()) {
                    $this->logger->warning("Error occurred when trying to process uploaded file");
                    ilUtil::sendFailure($this->plugin->txt("fileImportError_upload"), true);
                    $this->ctrl->redirectByClass(self::class, "gradesCsvImport");
                }

                if ($this->upload->hasBeenProcessed()) {
                    $uploadResult = $this->upload->getResults()[$form->getInput("csvFileImport")["tmp_name"]];
                }
            } catch (Exception $ex) {
                $this->logger->warning("Error occurred when trying to process uploaded file. Ex: {$ex->getMessage()}");
                ilUtil::sendFailure($this->plugin->txt("fileImportError_upload"), true);
                $this->ctrl->redirectByClass(self::class, "gradesCsvImport");
            }

            if ($uploadResult->getMimeType() !== "text/csv") {
                ilUtil::sendFailure($this->plugin->txt("fileImportError_invalidMimeType"), true);
                $this->ctrl->redirectByClass(self::class, "gradesCsvImport");
            }

            $gradesData = $this->convertCsvIntoModelArr($uploadResult->getPath());
            $importHistory = (new ImportHistory())
                ->setUserId((int) $this->user->getId())
                ->setDatasets(count($gradesData))
                ->setDate(new DateTime());

            if (!$this->importHistoryRepo->create($importHistory)) {
                $this->logger->warning("Error occurred when trying to save import history");
                ilUtil::sendFailure($this->plugin->txt("fileImportError_importHistory_not_created"), true);
                $this->ctrl->redirectByClass(self::class, "gradesCsvImport");
            }

            if (!$this->gradeDataRepo->import($gradesData)) {
                $this->logger->warning("Error occurred when trying to save grades data to database");
                ilUtil::sendFailure($this->plugin->txt("fileImportError_gradeData_not_imported"), true);
                $this->ctrl->redirectByClass(self::class, "gradesCsvImport");
            }

            $this->logger->info(
                sprintf(
                    "CSV Grades Import successful. %s entries were imported from the CSV file",
                    count($gradesData)
                )
            );
            ilUtil::sendSuccess(sprintf($this->plugin->txt("fileImportSuccess"), count($gradesData)), true);
            $this->ctrl->redirectByClass(self::class, "gradesCsvImport");
        }
        $this->mainTpl->setContent($form->getHTML());
    }

    /**
     * Calls the function for a received command
     * @param $cmd
     * @throws Exception
     */
    public function performCommand($cmd)
    {
        $this->injectTabs();

        $cmd = $cmd === "configure" ? $this->getDefaultCommand() : $cmd;

        if (method_exists($this, $cmd)) {
            $this->{$cmd}();
        } else {
            ilUtil::sendFailure(sprintf($this->plugin->txt("cmdNotFound"), $cmd));
            $this->{$this->getDefaultCommand()}();
        }
    }

    /**
     * Injects the tabs
     */
    protected function injectTabs()
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
            self::AGOP_CSV_IMPORT_SUBTAB,
            $this->plugin->txt("grades_csv_import"),
            $this->ctrl->getLinkTargetByClass(self::class, "gradesCsvImport")
        );

        $this->tabs->activateTab(self::AGOP_SETTINGS_TAB);
    }

    /**
     * Converts the data in the csv file into an array of GradeData objects
     * @param string $filePath
     * @return array
     */
    protected function convertCsvIntoModelArr(string $filePath) : array
    {
        if (is_readable($filePath) || is_resource($filePath)) {
            ilUtil::sendFailure($this->plugin->txt("fileImportError_fileNotAccessible"), true);
            $this->ctrl->redirectByClass(self::class, "gradesCsvImport");
        }
        $fileHandle = fopen($filePath, "r");

        //Plausibility check
        $row = 0;
        $nFields = 0;
        while (($data = fgetcsv($fileHandle, 0, self::AGOP_CSV_SEPARATOR)) !== false) {
            if ($row === 0) {
                $nFields = count($data);
                $row++;
                continue;
            }

            if (count($data) !== $nFields) {
                ilUtil::sendFailure($this->plugin->txt("fileImportError_plausiblityCheck_failed"), true);
                $this->ctrl->redirectByClass(self::class, "gradesCsvImport");
            }
            $row++;
        }

        //Conversion
        $row = 0;
        rewind($fileHandle);
        $gradesData = [];
        while (($data = fgetcsv($fileHandle, 0, self::AGOP_CSV_SEPARATOR)) !== false) {
            if ($row === 0) {
                $row++;
                continue;
            }

            $gradesData[] = (new GradeData())
                ->setNoteId((int) ($data[0] ?? 0))
                ->setMatrikel((string) ($data[1] ?? ""))
                ->setStg((string) ($data[2] ?? ""))
                ->setSubjectNumber((string) ($data[3] ?? ""))
                ->setSubjectShortName((string) ($data[4] ?? ""))
                ->setSubjectName((string) ($data[5] ?? ""))
                ->setSemester((int) ($data[6] ?? 0))
                ->setInstructorName((string) ($data[7] ?? ""))
                ->setType((string) ($data[8] ?? ""))
                ->setDate(DateTime::createFromFormat("d.m.Y", $data[9]))
                ->setGrade((float) ($data[10] ?? 0))
                ->setEvaluation((float) ($data[11] ?? 0))
                ->setAverageEvaluation((float) ($data[12] ?? 0))
                ->setCredits((float) ($data[13] ?? 0))
                ->setSeatNumber((int) ($data[14] ?? 0))
                ->setStatus((string) ($data[15] ?? ""))
                ->setSubjectAuthorization($data[16] === "true")
                ->setRemark((string) ($data[17] ?? ""))
                ->setCreatedAt(DateTime::createFromFormat("d.m.Y", $data[18]))
                ->setModifiedAt(DateTime::createFromFormat("d.m.Y", $data[19]));

            $row++;
        }
        fclose($fileHandle);
        return $gradesData;
    }

    /**
     * Returns the default command
     * @return string
     */
    protected function getDefaultCommand() : string
    {
        return "generalSettings";
    }
}
