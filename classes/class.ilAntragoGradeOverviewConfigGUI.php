<?php declare(strict_types=1);

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
     * @var ilSetting
     */
    protected $settings;
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
        $this->settings = new ilSetting(ilAntragoGradeOverviewPlugin::class);
        $this->upload = $this->dic->upload();
        $this->logger = $this->dic->logger()->root();
        $this->user = $this->dic->user();
        $this->gradeDataRepo = GradeDataRepository::getInstance($this->dic->database());
        $this->importHistoryRepo = ImportHistoryRepository::getInstance($this->dic->database());
        //Todo: Make sure config can only be accessed when the plugin is activated (no update required)
        $this->plugin = ilAntragoGradeOverviewPlugin::getInstance();
    }

    /**
     * Show the plugin settings form
     */
    public function generalSettings()
    {
        $this->tabs->activateSubTab(self::AGOP_GENERAL_SUBTAB);
        $form = new GeneralConfigForm();
        $this->mainTpl->setContent($form->getHTML());
    }

    public function save_generalSettings()
    {
        $this->tabs->activateSubTab(self::AGOP_GENERAL_SUBTAB);

        $form = new GeneralConfigForm();
        $form->setValuesByPost();
        if ($form->checkInput()) {
            $gradePassedThreshold = $form->getInput("gradePassedThreshold");
            $showMainMenu = (bool) $form->getInput("showMainMenuItem");

            $this->settings->set("gradePassedThreshold", $gradePassedThreshold);
            $this->settings->set("showMainMenuItem", $showMainMenu);

            ilUtil::sendSuccess($this->plugin->txt("updateSuccessful"), true);
            $this->ctrl->redirectByClass(self::class, $this->getDefaultCommand());
        }
        $this->mainTpl->setContent($form->getHTML());
    }

    /**
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

        $start = $pagination->getOffset();
        $stop = $pagination->getPageSize();

        $translation = "";
        if (($totalNumberOfElements) > 1) {
            $translation = sprintf($this->plugin->txt("answersFromTo"), $start + 1, $stop);
        }

        $html = '<div class="tmsq-pagination">' .
            $renderer->render($pagination)
            . '<hr class="tmsq-pagination-separator">'
            . '</div>';

        return [
            "html" => $html,
            "start" => $start,
            "currentPage" => $currentPage,
            "stop" => $stop
        ];
    }

    protected function applyFilter()
    {
        $table = new ImportHistoryTable($this);
        $table->writeFilterToSession();
        $table->resetOffset();
        $this->gradesCsvImport();
    }

    protected function resetFilter()
    {
        $table = new ImportHistoryTable($this);
        $table->resetOffset();
        $table->resetFilter();
        $this->gradesCsvImport();
    }

    public function save_gradesCsvImport()
    {
        $this->tabs->activateSubTab(self::AGOP_CSV_IMPORT_SUBTAB);
        $form = new CsvImportForm();
        $form->setValuesByPost();
        if ($form->checkInput()) {
            try {
                if ($this->upload->hasUploads() && !$this->upload->hasBeenProcessed()) {
                    $this->upload->process();
                } elseif (!$this->upload->hasUploads()) {
                    $this->logger->warning("Error occurred when trying to process uploaded file");
                    ilUtil::sendFailure($this->plugin->txt("fileImportError_upload"), true);
                    $this->ctrl->redirectByClass(self::class, "gradesCsvImport");
                }

                if ($this->upload->hasBeenProcessed()) {
                    $uploadResults = $this->upload->getResults();
                }
            } catch (Exception $ex) {
                $this->logger->warning("Error occurred when trying to process uploaded file. Ex: {$ex->getMessage()}");
                ilUtil::sendFailure($this->plugin->txt("fileImportError_upload"), true);
                $this->ctrl->redirectByClass(self::class, "gradesCsvImport");
            }

            if (count($uploadResults) > 1) {
                ilUtil::sendFailure($this->plugin->txt("fileImportError_moreThanOneFile"), true);
                $this->ctrl->redirectByClass(self::class, "gradesCsvImport");
            }

            $uploadResult = array_values($uploadResults)[0];
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

    protected function injectTabs()
    {
        $this->tabs->addTab(
            ilAntragoGradeOverviewConfigGUI::AGOP_SETTINGS_TAB,
            $this->lng->txt("settings"),
            ""
        );

        $this->tabs->setForcePresentationOfSingleTab(true);
        $this->tabs->addSubTab(
            ilAntragoGradeOverviewConfigGUI::AGOP_GENERAL_SUBTAB,
            $this->lng->txt("general_settings"),
            $this->ctrl->getLinkTargetByClass(ilAntragoGradeOverviewConfigGUI::class, "generalSettings")
        );

        $this->tabs->addSubTab(
            ilAntragoGradeOverviewConfigGUI::AGOP_CSV_IMPORT_SUBTAB,
            $this->plugin->txt("grades_csv_import"),
            $this->ctrl->getLinkTargetByClass(ilAntragoGradeOverviewConfigGUI::class, "gradesCsvImport")
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
        $fileHandle = fopen($filePath, "r");

        //Plausibility check
        $row = 0;
        $nFields = 0;
        while (($data = fgetcsv($fileHandle, 0, self::AGOP_CSV_SEPARATOR)) !== false) {
            if ($row == 0) {
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
            if ($row == 0) {
                $row++;
                continue;
            }

            $userByMatriculation = $this->plugin->findUserByMatriculation($data[1]);

            if ($userByMatriculation == null) {
                $this->logger->warning("No user found with the matriculation {$data[1]}. Import of that row will be skipped");
                $row++;
                continue;
            }

            $gradesData[] = (new GradeData())
                ->setNoteId((int) $data[0])
                ->setUserId((int) $userByMatriculation->getId())
                ->setMatrikel($data[1])
                ->setStg($data[2])
                ->setSubjectNumber($data[3])
                ->setSubjectShortName($data[4])
                ->setSubjectName($data[5])
                ->setSemester((int) $data[6])
                ->setInstructorName($data[7])
                ->setType($data[8])
                ->setDate(DateTime::createFromFormat("d.m.Y", $data[9]))
                ->setGrade((float) $data[10])
                ->setEvaluation((float) $data[11])
                ->setAverageEvaluation((float) $data[12])
                ->setCredits((float) $data[13])
                ->setSeatNumber((int) $data[14])
                ->setStatus($data[15])
                ->setSubjectAuthorization($data[16] === "true")
                ->setRemark($data[17])
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
