<?php

class NaturalToSql
{

    protected $sql;
    protected $action;
    protected $table;
    protected $where;
    protected $orderBy;
    protected $limit;

    public $result_type;
    public $error;

    protected $lib = [
        'action' => [
            'search' => 'select',
            'show' => 'select',
            'find' => 'select',
            'get' => 'select',
            'select' => 'select',
            'delete' => 'delete',
            'remove' => 'delete',
            'add' => 'insert',
            'insert' => 'insert',
            'update' => 'update',
            'fix' => 'update',
            'change' => 'update',
            'count' => 'count',
            'how many' => 'count',
            'how much' => 'count'
        ],
        'where' => [
            'where' => 'where',
            'who have' => 'where',
            'which have' => 'where',
            'who has' => 'where',
            'with' => 'where',
        ],
        'qty' => [
            'all' => '*',
            'everybody' => '*',
            'everything' => '*',
            'first' => '',
            'last' => '',
        ],
        'tables' => [
            'order' => 'orders',
            'user' => 'users',
            'vacancy' => 'vacancies'
        ]
    ];

    function __construct($str)
    {
        $this->str = $str;
        $this->strArr = explode(' ', $str);
        $this->action = $this->findAndDeleteAction();

        $this->findQty();

        $this->table = $this->findAndDeleteTable();

        $this->where = $this->findWhere();

        $this->process();

    }

    function findAndDeleteAction()
    {
        foreach ($this->lib['action'] as $search => $searchVal) {
            if (substr($this->str, 0, strlen($search)) == $search) {
                $action = $searchVal;
                $this->str = substr($this->str, strlen($search));
                break;
            }
        }
        return $action ?? reset($this->lib['action']);
    }

    function findQty()
    {
        foreach (['first', 'last'] as $search) {
            foreach ($this->strArr as $key => $word) {
                if ($search == $word) {
                    $next = $this->strArr[$key + 1] ?? 0;
                    $next = (int)$next;
                    if ($next) {
                        $limit = $next;
                    } else {
                        $limit = 1;
                    }
                    $this->limit = $limit;
                    if ($search == 'first') {
                        $this->orderBy = 'id ASC';
                    } else {
                        $this->orderBy = 'id DESC';
                    }
                    break;
                }
            }
        }
        return $action ?? reset($this->lib['qty']);
    }

    function findAndDeleteTable()
    {
        $tableArr = [
            'plural' => array_values($this->lib['tables']),
            'singular' => array_keys($this->lib['tables'])
        ];
        foreach ($tableArr as $form => $arr) {
            foreach ($arr as $search => $searchVal) {

                $pos = strpos($this->str, $searchVal . ' ');
                if ($pos !== false) {
                    $table = $searchVal;
                    if ($form == 'singular') {
                        $this->result_type = 'record';
                        $table = $this->lib['tables'][$searchVal];
                    }
                    $this->str = substr($this->str, $pos + strlen($search));
                    break;
                }
            }
        }

        return $table ?? '';
    }

    function findAndDelete($word)
    {
        foreach ($this->lib[$word] as $search => $searchVal) {
            $pos = strpos($this->str, $search);
            if ($pos !== false) {
                $action = $searchVal;
                $this->str = substr($this->str, $pos + strlen($search));
                break;
            }
        }
        return $action ?? '';
    }

    function findWhere()
    {
        foreach ($this->lib['where'] as $search => $searchVal) {
            $pos = strpos($this->str, $search . ' ');
            if ($pos !== false) {

                return substr($this->str, $pos + strlen($search));

            }
        }
    }


    function process()
    {
        if ($this->table) {
            switch ($this->action) {
                case 'select':
                    if ($this->limit == 1) {
                        $this->result_type = 'record';
                    }
                    $this->sql = 'SELECT * FROM ' .
                        $this->table .
                        ($this->where ? ' WHERE ' . $this->where : '') .
                        ($this->orderBy ? ' ORDER BY ' . $this->orderBy : '') .
                        ($this->limit ? ' LIMIT ' . $this->limit : '');

                    break;
                case 'count':
                    $this->result_type = 'value';

                    $this->sql = 'SELECT COUNT(*) FROM ' . $this->table . ($this->where ? ' WHERE ' . $this->where : '');
                    break;
                // case 'insert':
                // 	$this->sql = 'INSERT INTO ' . $this->table .  ($this->where ? ' WHERE ' . $this->where : '');
                // 	break;
                default:
                    # code...
                    break;
            }
            return $this->sql;
        } else {
            $this->error = 'No table recognized';
        }
    }

    function getQuery()
    {
        return $this->sql;
    }
}

if ($_POST['q']) {
    $app = new NaturalToSql($_POST['q'] . ' ');
    if ($app->error) {
        $result = ['status' => 'error', 'message' => $app->error];
    } else {
        $query = $app->getQuery();
        $type = $app->result_type ?? 'list';

        try {
            $pdo_options[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;
            $db = new PDO('mysql:host=localhost;dbname=natural-to-sql', 'root', '', $pdo_options);

            $req = $db->prepare($query);
            $req->execute();
            $dbResult = $req->fetchAll(PDO::FETCH_ASSOC);

            if ($app->result_type == 'value')
                $dbResult = array_values($dbResult[0])[0] ?? 'Error';

            $result = ['type' => $type, 'result' => $dbResult, 'query' => $query];
        } catch (Exception $e) {
            $result = ['status' => 'error', 'message' => $e->getMessage(), 'type' => $type, 'query' => $query];
        }
    }


    header('Content-Type: application/json');
    echo json_encode($result);
}