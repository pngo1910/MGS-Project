
<?php
  function retrieveColumns ($tablename, $options, $connection) {
    $sql = "SELECT column_name FROM information_schema.columns ".(($options===0)? "where table_name = ?" : "where table_name = ? $options");



    $stmt = sqlsrv_query($connection, $sql, array($tablename));
    if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

    $columns = array();
    while ($row = sqlsrv_fetch_array($stmt))
    {
      $columns[] = $row['column_name'];
      
    }

    return $columns;
  }

  function retrievePrimaryKeys ($tablename, $connection) {
    $sql = "SELECT column_name FROM information_schema.key_column_usage WHERE OBJECTPROPERTY(OBJECT_ID(constraint_name), 'IsPrimaryKey') = 1 AND table_name = ?";
    $stmt = sqlsrv_query($connection, $sql, array($tablename));
    if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

    $primaryKeys = array();
    while ($row = sqlsrv_fetch_array($stmt))
      $primaryKeys[] = $row['column_name'];

    return $primaryKeys;
  }

  function retrieveTableNames ($connection, $options) {
    $sql = "SELECT table_name FROM information_schema.tables WHERE table_type = 'base table' ".(($options===0)? "order by table_name" : "$options order by table_name");
    $stmt = sqlsrv_query($connection, $sql);
    if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);

    $tables = array();
    while ($row = sqlsrv_fetch_array($stmt))
      if (strtolower($row['table_name']) != 'sysdiagrams') $tables[] = $row['table_name'];

    return $tables;
  }

  function validateTableName ($tablename, $connection) {
    $sql = "SELECT table_name FROM information_schema.tables WHERE table_name = ?";
    $stmt = sqlsrv_query($connection, $sql, array($tablename));
    if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
    return sqlsrv_fetch_array($stmt)['table_name'];
  }
?>
