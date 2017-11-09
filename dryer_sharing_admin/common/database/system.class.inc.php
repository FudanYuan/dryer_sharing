<?php

//数据库连接
class ConnDB
{
    var $dbtype;
    var $host;
    var $user;
    var $pwd;
    var $dbname;

    //构造方法
    function ConnDB($dbtype, $host, $user, $pwd, $dbname)
    {
        $this->dbtype = $dbtype;
        $this->host = $host;
        $this->user = $user;
        $this->pwd = $pwd;
        $this->dbname = $dbname;
    }

    //实现数据库的连接并返回连接对象
    function GetConnld()
    {
        if ($this->dbtype == "mysql" || $this->dbtype == "mssql") {
            $dsn = "$this->dbtype:host=$this->host;dbname=$this->dbname";
        } else {
            $dsn = "$this->dbtype:dbname=$this->dbname";
        }
        try {
            $conn = new PDO($dsn, $this->user, $this->pwd);
            //初始化一个对象，就是创建了数据库连接对象$pdo
            $conn->query("set names utf8");
            return $conn;
        } catch (PDOException $e) {
            die("Error!:" . $e->getMessage() . "<br>");
        }
    }
}

//数据库管理类
class AdminDB
{
    function ExecSQL($sqlstr, $conn)
    {
        $sqltype = strtolower(substr(trim($sqlstr), 0, 6));
        $rs = $conn->prepare($sqlstr);
        $rs->execute();
        if ($sqltype == "select") {
            $array = $rs->fetchAll(PDO::FETCH_ASSOC);
            if (count($array) == 0 || $rs == false)
                return false;
            else
                return $array;

        } elseif ($sqltype == "update" || $sqltype == "insert" || $sqltype == "delete") {
            if ($rs)
                return true;
            else
                return false;

        }
    }

    function Transcation($sqlstr, $conn)
    {
        try {
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            //开始事务
            $conn->beginTransaction();
            for ($i = 0; $i < sizeof($sqlstr); $i++) {
                $conn->exec($sqlstr[$i]);
            }
            //提交事务
            $conn->commit();
            return true;
        } catch (PDOException $e) {
            //回滚事务
            $conn->rollBack();
            return false;
        }
    }
}
