<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <title>Cemetery Details</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
<style>
* {
  box-sizing: border-box;
}

/* Create two equal columns that floats next to each other */
.column {
  float: left;
  width: 40%;
  padding: 10px;
  height: 300px; /* Should be removed. Only for demonstration */
}

/* Clear floats after the columns */
.row:after {
  content: "";
  display: table;
  clear: both;
}
</style>
</head>
<body>

<h2>Cemetery detail for: </h2>

<div class="row">
  <div class="column" style="background-color:#aaa;">
    <h2></h2>
    <p><b>MGS Number:</b></p>
    <li><a href="https://mani.mbgenealogy.com/searchsource/book.php">MGS Indexed Books</a></li>
                <li><a href="https://mani.mbgenealogy.com/searchsource/cemetery.php">Cemeteries</a></li>
                <li><a href="https://mani.mbgenealogy.com/searchsource/church.php">Churches</a></li>
                <li><a href="https://mani.mbgenealogy.com/searchsource/funeralhomes.php">Funeral Homes</a></li>
  </div>
  <div class="column" style="background-color:#bbb;">
    <h2>Column 2</h2>
    <p>Some text..</p>
  </div>
</div>

</body>
</html>

  <body>
    <table border="2">
      <h1> Cemetery Details</h1>

      <caption>Table 6-1: HTML Markup Structure and Sequence</caption>
      <thead><tr><th>Element</th><th>Description</th></tr></thead>
      <!-- inside all table containers, you still use table rows -->
      <!-- this includes thead, tbody, and tfoot as shown here   -->
      <!-- Use th for bold headings in both header and footer    -->
      <tbody>
         <tr><td>table</td><td>overall table container</td></tr>
         <tr><td>caption</td><td>table caption text</td></tr>
         <tr><td>tbody</td><td>table body container</td></tr>
         <tr><td>tfoot</td><td>table footer container</td></tr>
       </tbody>
       <tfoot><tr><th>Element</th><th>Description</th></tr></tfoot>
    </table>
  </body>
</html>
