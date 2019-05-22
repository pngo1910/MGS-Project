
<?php
  $sql = "SELECT typeid, typedescr FROM typecodes";
  $stmt = sqlsrv_query($conn, $sql);
  if ($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
?>

<h3>Event Legend</h3>
  <div id="absolute">
  <table id="legend">
    <?php list($rownum, $start, $end) = array(0, 0, 0); ?>

    <?php while ($row = sqlsrv_fetch_array($stmt)) : ?>
      <?php if ($rownum % 5 === 0 && ++$start) : ?><tr><?php endif ?>
        <td><?= $row['typeid'] ?> = <?= $row['typedescr'] ?></td>
      <?php if (++$rownum % 5 === 5 && ++$end) : ?></tr><?php endif ?>
    <?php endwhile ?>

    <?php if ($start > $end) : ?>
      <?php while ($rownum++ % 5 != 0) : ?><td></td><?php endwhile ?></tr>
    <?php endif ?>
  </table>
</div>
