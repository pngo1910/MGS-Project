<script type="text/javascript">
    var asInitVals = new Array();

    $(document).ready(function() {
        //window.alert = function(){return null;};

        var tableData = new Array([]),
            rowNum = 0, //current row number
            ids = new Array(), //holds the ids
            tables = new Array(), //holds the tables
            index = 0; //index for ids and tables

        var payIndex = 0, //index for the payperview
            recordID = new Array(), //payperview RecordID
            tableName = new Array(), //payperview TableName
            fileName = new Array(), //payperview FileName
            pageNum = new Array(); //payperview PageNum
            
        var purchaseRecord = new Array(), //purchases RecordID
            purchaseTable = new Array(), //purchases TableName
            purchasePage = new Array(), //purchases PageNum
            purchaseIndex = 0; //index for the purchases
        //These are the indexes where the information for the pay-per-view and purchases appear in $rows, we don't want them showing up in the table
        var payperview = [8, 9, 10, 11],
            purchases = [12, 13];
           //for each row in the result set
        <?php foreach ($output['aaData'] as $key => $rows) : ?>
            //This is to stop the rows returned by these two tables from appearing in tableData
            <?php if($rows[16] != 'PayPerView' && $rows[16] != 'Purchases'): ?>
                //we go through all data per row
                var rows = new Array();
                //rows.push('<?php echo addslashes(json_encode($rows)) ?>');
                rows = '<?php echo addslashes(json_encode($rows)) ?>'.match(/(".*?"|[^",\s]+)(?=\s*,|\s*$)/g);//replace(/,null|,"/g,'\",\"').split("\",\"");//.replace("\",\"", ",").split(",");
                
                /* These two arrays are needed for the PayPerView and the records the user has purchased */
                //Keep track of all the ids
                ids[index] = <?= $rows[0] ?>;
                //Keep track of all the tables
                tables[index] = '<?= $rows[16] ?>';

                //Loop through the current row, skipping the id
                for(var i=1; i<rows.length -1; i++){
                    var payperviewMatch = false,
                        purchaseMatch = false;
                    //Loop through the row indexes we don't want (see the two arrays above)
                    //I chose to loop through payperview just because it has the most
                    for(var j = 0; j < payperview.length; j++){
                        //Check if the index equals the number in payperview or purchases
                        //If there's a match, set the flag for the matched value to true and end the loop
                        if(i == payperview[j]){
                            payperviewMatch = true;
                            j = payperview.length;
                        } else if(purchases[j] != undefined && i == purchases[j]) {
                            purchaseMatch = true;
                            j = payperview.length;
                        }
                    }
                    
                    rows[i] = rows[i].replace(/\[|\]|"/g,'');
                    //Check that there were no matches found
                    if(!payperviewMatch && !purchaseMatch){
                        //Add the row value to tableData
                        tableData[rowNum].push(rows[i]);
                    }
                }

                //The following two tableData push functions are so the values don't get mixed up
                //because Purchase PPV and e-Store won't be added until after all the rows are looped through
                tableData[rowNum].push('No Associated File Yet');
                tableData[rowNum].push('No Associated File Yet');
                //tableData[rowNum].push("<button>Information Page</button>");
                tableData.push([]);
                //Move on to the next row
                rowNum++;
                //Increment the index for ids and tables
                index++;
            <?php elseif($rows[16] == 'PayPerView'): ?>
                //Add the PayPerView information to the proper arrays
                tableName[payIndex] = "<?= $rows[9] ?>";
                recordID[payIndex] = <?= $rows[8] ?>;
                fileName[payIndex] = "<?= $rows[10] ?>";
                <?php if($rows[11] != ""): ?>
                    pageNum[payIndex] = <?= $rows[11] ?>;
                <?php else: ?>
                    pageNum[payIndex] = "";
                <?php endif; ?>
                //Increment the index for the PayPerView
                payIndex++;
            <?php else: ?>
                //Add the Purchase information to the proper arrays
                purchasePage[purchaseIndex] = '<?= $rows[11] ?>';
                purchaseRecord[purchaseIndex] = <?= $rows[12] ?>;
                purchaseTable[purchaseIndex] = '<?= $rows[13] ?>';
                //Increment the index for the Purchases
                purchaseIndex++;
            <?php endif; ?>
            
        <?php endforeach; ?>

        //remove the empty row that appears
        tableData.splice(tableData.length-1, 1);
        
        //Check that there's a value in recordID
        if(recordID.length != 0){
            //Loop through the recordID array
            for(var i = 0; i < recordID.length; i++){
                //Set the tableData index to 0
                var index = 0;
                //Loop through the ids
                for(var j = 0; j < ids.length; j++){
                    //Because the id and the table it's associated with were put into their arrays at the exact same position,
                    //it isn't necessary to also loop through the tables array
                    //Check if id equals RecordID and table equals TableName
                    if(ids[j] == recordID[i] && tables[j] == tableName[i]){
                        //Index 9 is where the e-Store is
                        tableData[index][9] = "<button onclick=\"e_store("+recordID[i]+", '"+tableName[i]+"', '"+fileName[i]+"')\">Purchase e-Store</button>";
                        if(pageNum[i] != ""){
                            //Index 10 is where the Purchases PPV is
                            tableData[index][10] = "<button onclick=\"purchase("+recordID[i]+", '"+tableName[i]+"', '"+fileName[i]+"', "+pageNum[i]+")\">Purchase PPV $1.00</button>";
                        }
                        //End the loop
                        j = ids.length;
                    } else {
                        //Increment the index
                        index++;
                    }
                }
            }
        }

        //Check that there's a value in purchaseRecord
        if(purchaseRecord.length != 0){
            //Loop through the purchaseRecord array
            for(var i = 0; i < purchaseRecord.length; i++){
                //Set the tableData index to 0
                var index = 0;
                //Loop through the ids
                for(var j = 0; j < ids.length; j++){
                    //Check if id equals purchaseRecord and table equals purchaseTable
                    if(ids[j] == purchaseRecord[i] && tables[j] == purchaseTable[i]){
                        if(purchasePage[i] != ''){
                            //Tell the user they've already purchased the PPV
                            tableData[index][10] = "<span class='purchased'>You have already purchased</span>";
                        } else {
                            //Tell the user they've already purchased the e-store
                            tableData[index][9] = "<span class='purchased'>You have already purchased</span>";
                        }
                        //End the loop
                        j = ids.length;
                    } else {
                        //Increment the index
                        index++;
                    }
                }
            }
        }
        
        var calcDataTableHeight = function() {
                return $(window).height()*55/100;
            }; 
        var start = 0;
        var oTable = $('#example').dataTable( {
            "scrollY": calcDataTableHeight(),
            "scrollCollapse": true,
            "scrollX": true,
            "bProcessing": true,
            "bPaginate": true,                  
            "bsortClasses": false,              
            "sPaginationType": 'full_numbers',
            "aLengthMenu": [ 10, 25, 50, 100, 500 ],
            "bFilter": true,
            "bInput" : true,
            "aaData": tableData,
            "aoColumns": [{"sTitle":"LastName"},{"sTitle":"FirstName"},{"sTitle":"Birth"},{"sTitle":"Death"},{"sTitle":"EventYear"},{"sTitle":"BookCode"},{"sTitle":"PageNumbers"},{"sTitle":"TypeCode"},{"sTitle":"SingleRecord"},{"sTitle":"e-Store/Other"},{"sTitle":"Pay-Per-View"}],//,{"sTitle":"Information"}
            "oLanguage": {
                "sSearch": "Search all columns:"
            }
        } );
        

        /*
        *   Found this function online http://www.hongkiat.com/blog/css-sticky-position/
        */

        var stickyNavTop = $('#legend').offset().top;

        var stickyNav = function(){
            var scrollTop = $(window).scrollTop();
                 
            if (scrollTop > stickyNavTop) { 
                $('#legend').addClass('sticky');
            } else {
                $('#legend').removeClass('sticky'); 
            }
        };

        stickyNav();

        $(window).scroll(function() {
            stickyNav();
        });
     
        $("tfoot input").keyup( function () {
            /* Filter on the column (the index) of this element */
            oTable.fnFilter( this.value, $("tfoot input").index(this) );
        } );

        /*
         * Support functions to provide a little bit of 'user friendlyness' to the textboxes in 
         * the footer
         */
        $("tfoot input").each( function (i) {
            asInitVals[i] = this.value;
        } );
        
        $("tfoot input").focus( function () {
            if ( this.className == "search_init" )
            {
                this.className = "";
                this.value = "";
            }
        } );
        
        $("tfoot input").blur( function (i) {
            if ( this.value == "" )
            {
                this.className = "search_init";
                this.value = asInitVals[$("tfoot input").index(this)];
            }
        } );
    } );

    function purchase(id, table, file, page) 
    {
        if(confirm("Are you sure you want to purchase this?"))
        {
            window.location="payperviewPurchase.php?id="+id+"&table="+table+"&fileName="+file+"&pageNum="+page;
        }
    }

    function e_store(id, table, file) 
    {
        window.open("e-store.php?id="+id+"&table="+table+"&fileName="+file,'_blank');
        //window.location="e-store.php?id="+id+"&table="+table+"&fileName="+file;
    }
</script>