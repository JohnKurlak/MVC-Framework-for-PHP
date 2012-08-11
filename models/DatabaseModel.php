<?php
class DatabaseModel extends Model {
	private static $db;
	private static $statement;
	
	public function __construct($databaseInfo) {
		try {
			self::$db = new PDO('mysql:host=' . $databaseInfo['hostname'] .
				';dbname=' . $databaseInfo['name'], $databaseInfo['username'],
				$databaseInfo['password']);
		} catch (PDOException $e) {
			trigger_error($e->getMessage(), E_USER_ERROR);
		}
	}
	
	public static function query($query) {
		self::$statement = self::$db->query($query);
		return self::$statement;
	}
	
	public static function unbufferedQuery($query) {
		self::$db->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
		self::$statement = self::$db->query($query);
		self::$db->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
		return self::$statement;
	}
	
	public static function prepare($query) {
		self::$statement = self::$db->prepare($query);
		return self::$statement;
	}
	
	public static function execute() {
		$insertValues = func_get_args();
		$num = func_num_args();
		
		if ($num === 0) {
			return self::$statement->execute();
		}
		else if ($num === 1 && is_array($insertValues[0])) {
			return self::$statement->execute($insertValues[0]);
		}
		else {
			return self::$statement->execute($insertValues);
		}
	}
	
	public static function unbufferedExecute() {
		$insertValues = func_get_args();
		self::$db->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
		$ret = self::execute($insertValues);
		self::$db->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
		return $ret;
	}
	
	public static function deleteRow($tableName, $fieldName, $fieldValue) {
		if (self::rowExists($tableName, $fieldName, $fieldValue)) {
			self::prepare("DELETE FROM `{$tableName}` WHERE `{$fieldName}` = ?");
			return self::unbufferedExecute($fieldValue);
		}
		else {
			return false;
		}
	}

	public static function insertID($table) {
		self::prepare("SELECT LAST_INSERT_ID() AS `insertID` FROM `{$table}`");
		$result = self::execute();
		$row = self::fetchAssoc();
		return $row['insertID'];
	}

	public static function rowExists(/* $tableName, $fieldName, $fieldValue[,
		$fieldName, $fieldValue[, ...]] */) {
		$args = func_get_args();
		$num = func_num_args();
		$tableName = $args[0];
		$values = array();
		$sql = "SELECT COUNT(*) AS `count` FROM `{$tableName}` WHERE ";

		for ($i = 1; $i < $num; $i += 2) {
			$fieldValue = $args[$i + 1];
			$fieldName = $args[$i];
			$sql .= "LOWER(`{$fieldName}`) = LOWER(?) AND ";
			$values[] = $fieldValue;
		}

		$sql = substr($sql, 0, -5);
		self::prepare($sql);
		$result = self::execute($values);
		$row = self::fetchAssoc();

		return (bool) $row['count'];
	}

	public static function numRows($tableOrStatement = '', $fieldName = '',
		$fieldValue = '') {
		self::prepare("SELECT COUNT(*) AS `num` FROM `$tableOrStatement` WHERE
			`$fieldName` = ?");
		self::execute($fieldValue);
		$row = self::fetchAssoc();
		return $row['num'];
	}
	
	public static function bindValue($parameter, $value, $dataType =
		PDO::PARAM_STR) {
		return self::$statement->bindValue($parameter, $value, $dataType);
	}
	
	public static function bindParam($parameter, $variable, $dataType =
		PDO::PARAM_STR, $length = 0, $driverOptions = null) {
		return self::$statement->bindParam($parameter, $variable, $dataType,
			$length, $driverOptions);
	}
	
	public static function fetchAssoc($statement = '') {
		if ($statement === '') {
			$row = self::$statement->fetch(PDO::FETCH_ASSOC);
			return $row;
		}
		else {
			return $statement->fetch(PDO::FETCH_ASSOC);
		}
	}
	
	public static function errorInfo() {
		return self::$db->errorInfo();
	}
}
?>