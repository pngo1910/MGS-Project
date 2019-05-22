<?php
	require(__DIR__.'/../db/memberCheck.php');
	require(__DIR__.'/../db/mgsConnection.php');

	function defaultFunction(){
		echo json_encode(['status'=>'success','data'=>'']);
	}

	function getTableSchema($conn){
		$infoSchema = 'INFORMATION_SCHEMA.COLUMNS';
		$result = [
			'status'=>'success',
			'data'=>''
		];
		if(empty($tableName = $_GET['tableName'])){
			$result = [
				'status'=>'error',
				'msg'=>'tableName empty!'
			];
			echo json_encode($result);
			exit;
		}
		$sql ="SELECT * from $infoSchema WHERE \"TABLE_NAME\"='$tableName'";
		if(!($stmt = sqlsrv_query($conn, $sql))){
			$result = [
				'status'=>'error',
				'msg'=>sprintf('error at %s in %s', __LINE__, __FILE__)
			];
			echo json_encode($result);
			exit;
		}
		$rows=[];
		while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)){
			$rows[] = $row;
		}
		$result['data'] = $rows;
		echo json_encode($result);
	}

	//function corresponds to all the functions defined above
	if(!function_exists($functionName=trim($_GET['function']))){
		defaultFunction();
		exit;
	}
	call_user_func($functionName,$conn);
?>