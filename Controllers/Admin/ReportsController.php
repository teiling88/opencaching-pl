<?php
namespace Controllers\Admin;

use Controllers\BaseController;
use Utils\Debug\Debug;
use lib\Objects\Admin\Report;
use lib\Objects\ChunkModels\PaginationModel;
use lib\Objects\OcConfig\OcConfig;

class ReportsController extends BaseController
{

    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        // Check if user is logged and has admin rights
        if (! $this->loggedUser) {
            $this->redirectToLoginPage();
            exit();
        } elseif (! $this->loggedUser->isAdmin()) {
            $this->view->redirect('\\');
            exit();
        }
        $this->view->setVar('user', $this->loggedUser);
        $this->view->loadJQuery();

        if (isset($_REQUEST['action'])) {
            switch ($_REQUEST['action']) {
                case 'showreport':
                    if (isset($_REQUEST['id']) && Report::isValidReportId($_REQUEST['id'])) {
                        $this->showSingleReport($_REQUEST['id']);
                    } else {
                        $this->view->setVar('errorMsg', tr('admin_reports_err_noID'));
                    }
                    break;
            }
        }
        $this->showReportsList();
    }

    private function showReportsList()
    {
        if (isset($_REQUEST['reportId']) && ! empty($_REQUEST['reportId']) && ! isset($_REQUEST['reset'])) {
            if (Report::isValidReportId($_REQUEST['reportId'])) {
                $this->showSingleReport($_REQUEST['reportId']);
            } else {
                $this->view->setVar('errorMsg', tr('admin_reports_err_noID'));
            }
        }
        if (isset($_REQUEST['reset'])) {
            $this->resetSession();
        } else {
            $this->setSession();
        }
        $paginationModel = new PaginationModel(Report::REPORTS_PER_PAGE);
        $reportsCount = Report::getReportsCounts($this->loggedUser, $_SESSION['reportWp'], $_SESSION['reportType'], $_SESSION['reportStatus'], $_SESSION['reportUser']);
        $paginationModel->setRecordsCount($reportsCount);
        list ($limit, $offset) = $paginationModel->getQueryLimitAndOffset();
        $reports = Report::getReports($this->loggedUser, $_SESSION['reportWp'], $_SESSION['reportType'], $_SESSION['reportStatus'], $_SESSION['reportUser'], $offset, $limit);
        $this->view->setVar('paginationModel', $paginationModel);
        $this->view->setVar('reports', $reports);
        $this->view->setVar('reportsCount', $reportsCount);
        $this->view->setVar('dateFormat',OcConfig::instance()->getDbDateTimeFormat());
        $this->view->setVar('typeSelect', Report::generateTypeSelect($_SESSION['reportType']));
        $this->view->setVar('statusSelect', Report::generateStatusSelect($_SESSION['reportStatus']));
        $this->view->setVar('userSelect', Report::generateUserSelect(false, $_SESSION['reportUser']));
        tpl_set_tplname('admin/reports_list');
        tpl_BuildTemplate();
        exit();
    }

    private function showSingleReport($id)
    {
        $report = new Report(array('reportId' => $id));
        if ($report->getId() == null) {
            Debug::errorLog('Attempt to show single report with incorrect report ID: ' . $id);
            $this->view->redirect('/admin_reports.php');
            exit();
        }
        $this->view->setVar('report', $report);
        $this->view->setVar('dateFormat',OcConfig::instance()->getDbDateTimeFormat());
        tpl_set_tplname('admin/report_show');
        tpl_BuildTemplate();
        exit();
    }

    private function setSession()
    {
        if (isset($_REQUEST['reportType'])) {
            $_SESSION['reportType'] = (int) $_REQUEST['reportType'];
        } elseif (! isset($_SESSION['reportType'])) {
            $_SESSION['reportType'] = Report::DEFAULT_REPORTS_TYPE;
        }
        if (isset($_REQUEST['reportStatus'])) {
            $_SESSION['reportStatus'] = (int) $_REQUEST['reportStatus'];
        } elseif (! isset($_SESSION['reportStatus'])) {
            $_SESSION['reportStatus'] = Report::DEFAULT_REPORTS_STATUS;
        }
        if (isset($_REQUEST['reportUser'])) {
            $_SESSION['reportUser'] = (int) $_REQUEST['reportUser'];
        } elseif (! isset($_SESSION['reportUser'])) {
            $_SESSION['reportUser'] = Report::DEFAULT_REPORTS_USER;
        }
        if (isset($_REQUEST['reportWp']) && $_REQUEST != '') {
            $_SESSION['reportWp'] = $_REQUEST['reportWp'];
        } elseif (! isset($_SESSION['reportWp'])) {
            $_SESSION['reportWp'] = '';
        }
    }
    private function resetSession()
    {
        $_SESSION['reportType'] = Report::DEFAULT_REPORTS_TYPE;
        $_SESSION['reportStatus'] = Report::DEFAULT_REPORTS_STATUS;
        $_SESSION['reportUser'] = Report::DEFAULT_REPORTS_USER;
        $_SESSION['reportWp'] = '';
    }
}