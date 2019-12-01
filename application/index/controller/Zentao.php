<?php


namespace app\index\controller;


class Zentao
{
    const ztURL        = '';           // ZenTaoPMS deploys domain names.
    const ztAccount    = '';           // ZenTaoPMS login account.
    const ztPassword   = '';           // ZenTaoPMS login password.
    const ztAccessMode = '';           // Parameter request method. [GET|PATH_INFO]

    public $params = array();           // Interface request parameter.
    public $tokenAuth = '';             // Session authentication.
    public $sessionRand = 0;            // Session random number for some encryption and verification.
    public $requestMethod = '';         // Set request method.
    public $returnResult = array('status' => 0, 'msg' => 'error', 'result' => array());         // Return result.

    /**
     * Get the session ID required for the session.
     *
     * @access public
     * @return void
     */
    public function __construct()
    {
        $this->login();
    }

    /**
     * User login verification.
     *
     * @access public
     * @return void
     */
    public function login()
    {
        /* Get token. */
        $result          = $this->setParams(array('m' => 'api', 'f' => 'getSessionID'))
            ->setRequestMethod('get')
            ->sendRequest(array('get' => self::ztURL), true);
        $responseData    = json_decode($result->data);
        $this->tokenAuth = $responseData->sessionName . '=' . $responseData->sessionID;

        /* User authentication login. */
        $this->setParams(array('account' => self::ztAccount, 'password' => self::ztPassword))
            ->setRequestMethod('post')
            ->sendRequest(array('get' => self::ztURL . '?m=user&f=login&t=json', 'path_info' => self::ztURL . '/user-login.json'));
    }

    /**
     * Get a list of departments.
     *
     * @param array $optionalParams
     * @access public
     * @return string
     */
    public function deptBrowse($optionalParams = array())
    {
        $responseData = $this->setParams(array('m' => 'dept', 'f' => 'browse'), $optionalParams)
            ->setRequestMethod('get')
            ->sendRequest(array('get' => self::ztURL), true);

        $returnResult = $this->returnResult;
        if (strcmp($responseData->status, 'success') === 0)
        {
            $responseData           = json_decode($responseData->data);
            $returnResult['status'] = 1;
            $returnResult['msg']    = 'success';
            $returnResult['result'] = array(
                'title'       => $responseData->title,
                'deptID'      => $responseData->deptID,
                'parentDepts' => $responseData->parentDepts,
                'sons'        => $responseData->sons,
                'tree'        => $responseData->tree
            );
        }

        return json_encode($returnResult, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Add a new department.
     *
     * @param array $optionalParams
     * @access public
     * @return string
     */
    public function deptManageChild($optionalParams = array())
    {
        $result = $this->setParams(array(), $optionalParams)
            ->setRequestMethod('post')
            ->sendRequest(array('get' => self::ztURL . '?m=dept&f=manageChild&t=json', 'path_info' => self::ztURL . '/dept-manageChild.json'));

        $returnResult = $this->returnResult;
        if (strpos($result, 'reload')) $returnResult = array('status' => 1, 'msg' => 'success', 'result' => array());

        return json_encode($returnResult, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Get user list.
     *
     * @param array $optionalParams
     * @access public
     * @return string
     */
    public function companyBrowse($optionalParams = array())
    {
        $responseData = $this->setParams(array('m' => 'company', 'f' => 'browse'), $optionalParams)
            ->setRequestMethod('get')
            ->sendRequest(array('get' => self::ztURL), true);

        $returnResult = $this->returnResult;
        if (strcmp($responseData->status, 'success') === 0)
        {
            $responseData           = json_decode($responseData->data);
            $returnResult['status'] = 1;
            $returnResult['msg']    = 'success';
            $returnResult['result'] = array('title' => $responseData->title, 'users' => $responseData->users);
        }

        return json_encode($returnResult, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Add user optional information.
     *
     * @param array $optionalParams
     * @access public
     * @return string
     */
    public function userCreateInfo($optionalParams = array())
    {
        $responseData = $this->setParams(array('m' => 'user', 'f' => 'create'), $optionalParams)
            ->setRequestMethod('get')
            ->sendRequest(array('get' => self::ztURL), true);

        $returnResult = $this->returnResult;
        if (strcmp($responseData->status, 'success') === 0)
        {
            $responseData           = json_decode($responseData->data);
            $returnResult['status'] = 1;
            $returnResult['msg']    = 'success';
            $returnResult['result'] = array(
                'title'     => $responseData->title,
                'depts'     => $responseData->depts,
                'groupList' => $responseData->groupList,
                'roleGroup' => $responseData->roleGroup
            );
            $this->sessionRand      = $responseData->rand;
        }

        return json_encode($returnResult, JSON_UNESCAPED_UNICODE);
    }

    /**
     * New users.
     *
     * @param array $optionalParams
     * @access public
     * @return string
     */
    public function userCreate($optionalParams = array())
    {
        //Get the random number required for encryption.
        $this->userCreateInfo();

        $optionalParams['password1']      = md5($optionalParams['password1'] . $this->sessionRand);
        $optionalParams['password2']      = md5($optionalParams['password2'] . $this->sessionRand);
        $optionalParams['verifyPassword'] = md5(md5(self::ztPassword) . $this->sessionRand);
        $requestURL['get']                = self::ztURL . '?m=user&f=create&dept=' . $optionalParams['dept'] . '&t=json';
        $requestURL['path_info']          = self::ztURL . '/user-create-' . $optionalParams['dept'] . '.json';

        $responseData = $this->setParams(array(), $optionalParams)
            ->setRequestMethod('post')
            ->sendRequest($requestURL, true);

        $returnResult = $this->returnResult;
        if (strcmp($responseData->result, 'success') === 0) $returnResult = array('status' => 1, 'msg' => 'success');
        $returnResult['result'] = $responseData->message;

        return json_encode($returnResult, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Get product list.
     *
     * @param array $optionalParams
     * @access public
     * @return string
     */
    public function productAll($optionalParams = array())
    {
        $responseData = $this->setParams(array('m' => 'product', 'f' => 'all'), $optionalParams)
            ->setRequestMethod('get')
            ->sendRequest(array('get' => self::ztURL), true);

        $returnResult = $this->returnResult;
        if (strcmp($responseData->status, 'success') === 0)
        {
            $responseData           = json_decode($responseData->data);
            $returnResult['status'] = 1;
            $returnResult['msg']    = 'success';
            $returnResult['result'] = array(
                'title'        => $responseData->title,
                'products'     => $responseData->products,
                'productStats' => $responseData->productStats
            );
        }

        return json_encode($returnResult, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Get added product optional information.
     *
     * @param array $optionalParams
     * @access public
     * @return string
     */
    public function productCreateInfo($optionalParams = array())
    {
        $responseData = $this->setParams(array('m' => 'product', 'f' => 'create'), $optionalParams)
            ->setRequestMethod('get')
            ->sendRequest(array('get' => self::ztURL), true);

        $returnResult = $this->returnResult;
        if (strcmp($responseData->status, 'success') === 0)
        {
            $responseData           = json_decode($responseData->data);
            $returnResult['status'] = 1;
            $returnResult['msg']    = 'success';
            $returnResult['result'] = array(
                'title'    => $responseData->title,
                'products' => $responseData->products,
                'lines'    => $responseData->lines,
                'poUsers'  => $responseData->poUsers,
                'qdUsers'  => $responseData->qdUsers,
                'rdUsers'  => $responseData->rdUsers,
                'groups'   => $responseData->groups
            );
        }

        return json_encode($returnResult, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Add a single product.
     *
     * @param array $optionalParams
     * @access public
     * @return string
     */
    public function productCreate($optionalParams = array())
    {
        $requestURL['get']       = self::ztURL . '?m=product&f=create&t=json';
        $requestURL['path_info'] = self::ztURL . '/product-create.json';
        $responseData            = $this->setParams(array(), $optionalParams)
            ->setRequestMethod('post')
            ->sendRequest($requestURL, true);

        $returnResult = $this->returnResult;
        if (strcmp($responseData->result, 'success') === 0) $returnResult = array('status' => 1, 'msg' => 'success');
        $returnResult['result'] = $responseData->message;

        return json_encode($returnResult, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Get item list.
     *
     * @param array $optionalParams
     * @access public
     * @return string
     */
    public function projectAll($optionalParams = array())
    {
        $responseData = $this->setParams(array('m' => 'project', 'f' => 'all'), $optionalParams)
            ->setRequestMethod('get')
            ->sendRequest(array('get' => self::ztURL), true);

        $returnResult = $this->returnResult;
        if (strcmp($responseData->status, 'success') === 0)
        {
            $responseData           = json_decode($responseData->data);
            $returnResult['status'] = 1;
            $returnResult['msg']    = 'success';
            $returnResult['result'] = array(
                'title'        => $responseData->title,
                'projects'     => $responseData->projects,
                'projectStats' => $responseData->projectStats,
                'teamMembers'  => $responseData->teamMembers,
                'users'        => $responseData->users
            );
        }

        return json_encode($returnResult, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Get optional information for adding items.
     *
     * @param array $optionalParams
     * @access public
     * @return string
     */
    public function projectCreateInfo($optionalParams = array())
    {
        $responseData = $this->setParams(array('m' => 'project', 'f' => 'create'), $optionalParams)
            ->setRequestMethod('get')
            ->sendRequest(array('get' => self::ztURL), true);

        $returnResult = $this->returnResult;
        if (strcmp($responseData->status, 'success') === 0)
        {
            $responseData           = json_decode($responseData->data);
            $returnResult['status'] = 1;
            $returnResult['msg']    = 'success';
            $returnResult['result'] = array(
                'title'       => $responseData->title,
                'projects'    => $responseData->projects,
                'groups'      => $responseData->groups,
                'allProducts' => $responseData->allProducts
            );
        }

        return json_encode($returnResult, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Add a single item.
     *
     * @param array $optionalParams
     * @access public
     * @return string
     */
    public function projectCreate($optionalParams = array())
    {
        $requestURL['get']       = self::ztURL . '?m=project&f=create&t=json';
        $requestURL['path_info'] = self::ztURL . '/project-create.json';

        $responseData = $this->setParams(array(), $optionalParams)
            ->setRequestMethod('post')
            ->sendRequest($requestURL, true);

        $returnResult = $this->returnResult;
        if (strcmp($responseData->result, 'success') === 0) $returnResult = array('status' => 1, 'msg' => 'success');
        $returnResult['result'] = $responseData->message;

        return json_encode($returnResult, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Get task list.
     *
     * @param array $optionalParams
     * @access public
     * @return string
     */
    public function projectTask($optionalParams = array())
    {
        $responseData = $this->setParams(array('m' => 'project', 'f' => 'task'), $optionalParams)
            ->setRequestMethod('get')
            ->sendRequest(array('get' => self::ztURL), true);

        $returnResult = $this->returnResult;
        if (strcmp($responseData->status, 'success') === 0)
        {
            $responseData           = json_decode($responseData->data);
            $returnResult['status'] = 1;
            $returnResult['msg']    = 'success';
            $returnResult['result'] = array(
                'title'    => $responseData->title,
                'projects' => $responseData->projects,
                'project'  => $responseData->project,
                'products' => $responseData->products,
                'tasks'    => $responseData->tasks
            );
        }

        return json_encode($returnResult, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Add task optional information.
     *
     * @param array $optionalParams
     * @access public
     * @return string
     */
    public function taskCreateInfo($optionalParams = array())
    {
        $responseData = $this->setParams(array('m' => 'task', 'f' => 'create'), $optionalParams)
            ->setRequestMethod('get')
            ->sendRequest(array('get' => self::ztURL), true);

        $returnResult = $this->returnResult;
        if (strcmp($responseData->status, 'success') === 0)
        {
            $responseData           = json_decode($responseData->data);
            $returnResult['status'] = 1;
            $returnResult['msg']    = 'success';
            $returnResult['result'] = array(
                'title'            => $responseData->title,
                'projects'         => $responseData->projects,
                'users'            => $responseData->users,
                'stories'          => $responseData->stories,
                'moduleOptionMenu' => $responseData->moduleOptionMenu,
                'project'          => $responseData->project
            );
        }

        return json_encode($returnResult, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Add a single task.
     *
     * @param array $optionalParams
     * @access public
     * @return string
     */
    public function taskCreate($optionalParams = array())
    {
        $requestURL['get']       = self::ztURL . '?m=task&f=create&project=' . $optionalParams['project'] . '&t=json';
        $requestURL['path_info'] = self::ztURL . '/task-create-' . $optionalParams['project'] . '.json';

        $responseData = $this->setParams(array('status' => 'wait', 'after' => 'toTaskList'), $optionalParams)
            ->setRequestMethod('post')
            ->sendRequest($requestURL, true);

        $returnResult = $this->returnResult;
        if (strcmp($responseData->result, 'success') === 0) $returnResult = array('status' => 1, 'msg' => 'success');
        $returnResult['result'] = $responseData->message;

        return json_encode($returnResult, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Optional information for completing a single task.
     *
     * @param array $optionalParams
     * @access public
     * @return string
     */
    public function taskFinishInfo($optionalParams = array())
    {
        $responseData = $this->setParams(array('m' => 'task', 'f' => 'finish'), $optionalParams)
            ->setRequestMethod('get')
            ->sendRequest(array('get' => self::ztURL), true);

        $returnResult = $this->returnResult;
        if (strcmp($responseData->status, 'success') === 0)
        {
            $responseData           = json_decode($responseData->data);
            $returnResult['status'] = 1;
            $returnResult['msg']    = 'success';
            $returnResult['result'] = array(
                'title'   => $responseData->title,
                'users'   => $responseData->users,
                'task'    => $responseData->task,
                'project' => $responseData->project,
                'actions' => $responseData->actions
            );
        }

        return json_encode($returnResult, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Complete a single task.
     *
     * @param array $optionalParams
     * @access public
     * @return string
     */
    public function taskFinish($optionalParams = array())
    {
        $taskID = $optionalParams['taskID'];
        unset($optionalParams['taskID']);
        $requestURL['get']       = self::ztURL . '?m=task&f=finish&taskID=' . $taskID . '&t=json';
        $requestURL['path_info'] = self::ztURL . '/task-finish-' . $taskID . '.json';

        $result = $this->setParams(array('status' => 'done'), $optionalParams)
            ->setRequestMethod('post')
            ->sendRequest($requestURL, false);

        $returnResult = $this->returnResult;
        if (strpos($result, 'task-view-' . $taskID . '.json') || strpos($result, 'taskID=' . $taskID)) $returnResult = array('status' => 1, 'msg' => 'success', 'result' => array());

        return json_encode($returnResult, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Get BUG List.
     *
     * @param array $optionalParams
     * @access public
     * @return string
     */
    public function bugBrowse($optionalParams = array())
    {
        $responseData = $this->setParams(array('m' => 'bug', 'f' => 'browse'), $optionalParams)
            ->setRequestMethod('get')
            ->sendRequest(array('get' => self::ztURL), true);

        $returnResult = $this->returnResult;
        if (strcmp($responseData->status, 'success') === 0)
        {
            $responseData           = json_decode($responseData->data);
            $returnResult['status'] = 1;
            $returnResult['msg']    = 'success';
            $returnResult['result'] = array(
                'title'       => $responseData->title,
                'products'    => $responseData->products,
                'productID'   => $responseData->productID,
                'productName' => $responseData->productName,
                'product'     => $responseData->product,
                'moduleName'  => $responseData->moduleName,
                'modules'     => $responseData->modules,
                'browseType'  => $responseData->browseType,
                'bugs'        => $responseData->bugs
            );
        }

        return json_encode($returnResult, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Add single BUG optional information.
     *
     * @param array $optionalParams
     * @access public
     * @return string
     */
    public function bugCreateInfo($optionalParams = array())
    {
        $responseData = $this->setParams(array('m' => 'bug', 'f' => 'create'), $optionalParams)
            ->setRequestMethod('get')
            ->sendRequest(array('get' => self::ztURL), true);

        $returnResult = $this->returnResult;
        if (strcmp($responseData->status, 'success') === 0)
        {
            $responseData           = json_decode($responseData->data);
            $returnResult['status'] = 1;
            $returnResult['msg']    = 'success';
            $returnResult['result'] = array(
                'title'            => $responseData->title,
                'productID'        => $responseData->productID,
                'productName'      => $responseData->productName,
                'projects'         => $responseData->projects,
                'moduleOptionMenu' => $responseData->moduleOptionMenu,
                'users'            => $responseData->users,
                'stories'          => $responseData->stories,
                'builds'           => $responseData->builds
            );
        }

        return json_encode($returnResult, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Add a single bug.
     *
     * @param array $optionalParams
     * @access public
     * @return string
     */
    public function bugCreate($optionalParams = array())
    {
        $requestURL['get']       = self::ztURL . '?m=bug&f=create&productID=' . $optionalParams['product'] . '&t=json';
        $requestURL['path_info'] = self::ztURL . '/bug-create-' . $optionalParams['product'] . '.json';

        $responseData = $this->setParams(array('status' => 'active'), $optionalParams)
            ->setRequestMethod('post')
            ->sendRequest($requestURL, true);

        $returnResult = $this->returnResult;
        if (strcmp($responseData->result, 'success') === 0) $returnResult = array('status' => 1, 'msg' => 'success');
        $returnResult['result'] = $responseData->message;

        return json_encode($returnResult, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Optional information for solving a single bug.
     *
     * @param array $optionalParams
     * @access public
     * @return string
     */
    public function bugResolveInfo($optionalParams = array())
    {
        $responseData = $this->setParams(array('m' => 'bug', 'f' => 'resolve'), $optionalParams)
            ->setRequestMethod('get')
            ->sendRequest(array('get' => self::ztURL), true);

        $returnResult = $this->returnResult;
        if (strcmp($responseData->status, 'success') === 0)
        {
            $responseData           = json_decode($responseData->data);
            $returnResult['status'] = 1;
            $returnResult['msg']    = 'success';
            $returnResult['result'] = array(
                'title'    => $responseData->title,
                'products' => $responseData->products,
                'bug'      => $responseData->bug,
                'users'    => $responseData->users,
                'builds'   => $responseData->builds,
                'actions'  => $responseData->actions
            );
        }

        return json_encode($returnResult, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Solve a single bug.
     *
     * @param array $optionalParams
     * @access public
     * @return string
     */
    public function bugResolve($optionalParams = array())
    {
        $bugID                   = $optionalParams['bugID'];
        $requestURL['get']       = self::ztURL . '?m=bug&f=resolve&bugID=' . $bugID . '&t=json';
        $requestURL['path_info'] = self::ztURL . '/bug-resolve-' . $bugID . '.json';
        unset($optionalParams['bugID']);

        $result = $this->setParams(array('status' => 'resolved'), $optionalParams)
            ->setRequestMethod('post')
            ->sendRequest($requestURL);

        $returnResult = $this->returnResult;
        if (strpos($result, 'bug-view-' . $bugID . '.json') || strpos($result, 'bugID=' . $bugID)) $returnResult = array('status' => 1, 'msg' => 'success', 'result' => array());

        return json_encode($returnResult, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Send a get request.
     *
     * @param string $url
     * @access public
     * @return string
     */
    public function getUrl($url)
    {
        $ch = curl_init();
        if (self::ztAccessMode == 'GET')
        {
            $this->params = array_merge($this->params, array('t' => 'json'));
            if (!empty($this->params) && count($this->params))
            {
                $url .= strpos($url, '?') ? http_build_query($this->params) : '?' . http_build_query($this->params);
            }
        } elseif (self::ztAccessMode == 'PATH_INFO')
        {
            $params = implode('-', $this->params);
            $url    = $url . '/' . $params . '.json';
        }
        curl_setopt($ch, CURLOPT_COOKIE, $this->tokenAuth);
        curl_setopt($ch, CURLOPT_REFERER, self::ztURL);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    /**
     * Send a post request.
     *
     * @param string $url
     * @access public
     * @return string
     */
    public function postUrl($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_COOKIE, $this->tokenAuth);
        curl_setopt($ch, CURLOPT_REFERER, self::ztURL);
        curl_setopt($ch, CURLOPT_URL, $url);
        if (count($this->params)) curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($this->params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, true);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    /**
     * Processing request parameters.
     *
     * @param array $requiredParams
     * @param array $optionalParams
     * @access public
     * @return $this
     */
    public function setParams($requiredParams = array(), $optionalParams = array())
    {
        $this->params = array();
        $this->params = array_merge($requiredParams, $optionalParams);
        return $this;
    }

    /**
     * Set request method.
     *
     * @param string $requestMethod
     * @access public
     * @return $this
     */
    public function setRequestMethod($requestMethod = 'get')
    {
        $this->requestMethod = strcmp($requestMethod, 'get') === 0 ? 'get' : 'post';
        return $this;
    }

    /**
     * Send a request for response results.
     *
     * @param array $requestURL
     * @access public
     * @return string $result
     */
    public function sendRequest($requestURL = array(), $isJson = false)
    {
        if ($this->requestMethod == 'get') $result = $this->getUrl($requestURL['get']);
        if ($this->requestMethod == 'post') $result = self::ztAccessMode == 'GET' ? $this->postUrl($requestURL['get']) : $this->postUrl($requestURL['path_info']);
        $result = $isJson ? json_decode($result) : $result;
        return $result;
    }
}