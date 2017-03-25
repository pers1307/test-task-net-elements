<?php

namespace api\controllers;

use api\modules\core\base\ApiController;
use common\repository\storage\OrderStorageRepository;
use common\repository\storage\ProjectRepository;

class ProjectController extends ApiController
{
    const ERROR_INCORRECT_FIELDS = 1000;

    public function beforeAction($action)
    {
        return parent::beforeAction($action);
    }

    /**
     * /api/project.get-all
     *
     * @return response.data.success || response.data.projects
     */
    public function actionGetAll()
    {
        $projectRepository = new ProjectRepository();

        $projects = $projectRepository->getAllNameAndId();

        $orderStorageRepository = new OrderStorageRepository();

        foreach ($projects as &$project) {

            $project['lastGenerateDay'] = $orderStorageRepository->getProjectLastOrder($project['id']);
        }

        $this->addData([
            'success'  => true,
            'projects' => $projects
        ]);
    }
}