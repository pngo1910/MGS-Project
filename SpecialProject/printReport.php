<?php
/*
 * This script is used to pull all data from the Cemeteries and Cemetery Transcriptions tables and format it into a report that
 * is then saved as a PDF document. 
 * The report includes: - Title page that contains cemetery information.
 *                      - Historical write up of the cemetery with pictures of said cemetery.
 *                      - Index page that lists the surname, middle name and given name of each person buried in the cemetery
 *                        and what page they can be found on.
 *                       - Cemetery transcriptions that provide information on each burial plot.
 *
 * Authors: Jade Carpenter-Buhler, Matthew Isliefson
 * Version: 1.0
 * Date: March 16, 2017
 *
 * Updated: March 21, 2017: Fixed issue with the outputted data. Cemetery name, code and description now print once with all 
 *                          related data underneath. 
 *                          Data now prints properly, we are just waiting on the format guidelines.
 *
 * Updated: March 23, 2017: Cemetery transcription data is now displayed properly according to the template given to us.
 *                          Added the Header() function. This function overwrites the built in FPDF function to display the 
 *                          necessary cemetery information at the top of each page.
 *
 * Updated: March 29, 2017: Added the type of stone between plot and surname as specified by the client.
 *                          Added the Footer() function. This function overwrites the built in FPDF function to display page 
 *                          numbers on the bottom of each page.  
 *    
 * Updated: March 30, 2017: Title page now displays appropriate data in the format specified by the client.
 *
 * Updated: March 31, 2017: Finished the title page, we are unable to bold the specified words in the last paragraph near the 
 *                          bottom of the 
 *                          page. Other than that everything regarding the title page is done.
 *                          Began working on the historic write up page that follows the title page.   
 *
 * Updated: April 3, 2017: Added the PDF_TOC class in order to insert a table of contents. The base class PDF now extends 
 *                         PDF_TOC which in
 *                         turn extends the FPDF class.   
 *                         Table of contents now displays surnames and their associated page numbers.  
 *                         Fixed issue with the section and row numbers not appearing if they section ran onto the next page.
 *
 * Updated: April 11, 2017: Section and row numbers no longer appear on the index page.
 *                          All aspects of the reports are finished except the insertion of cemetery images.
 *
 * Last Updated: April 21, 2017: Fixed a few formatting issues that arose when new data was entered into cemeteries and cemetery
 *                               transcriptions.
 */

    //This breaks the script for some reason
  	//require('../errorReporter.php');

    //We are using the external fpdf library in order to create the downloadable pdfs.
    require('reportToc.php');

    //In order to override the header function of FPDF we need to subclass the FPDF class.
    //This PDF subclass of PDF_TOC contains the overwritten header function.
    class PDF extends PDF_TOC
    {
      //Overwrites the built in FPDF function.
      //Used to display cemetery information across multiple pages.
      function Header()
      {
        $this->SetFont('Times','B',15);

        //If the current page number is greater than 2 diisplay the cemetery name, legal description, municipality, cemetery code, section number and row number in the header.
        //This will allow the header to print on every page but the title page, historic write-up page and index page.
        if ($this->PageNo() > 2) 
        {
          // Move to the right
          $this->Cell(40);

          $this->Cell(45);
          $this->Cell(20,10,$GLOBALS['cemName'],0,0,'C');
          $this->Ln(10);
          $this->Cell(80);
          $this->Cell(30,10, $GLOBALS['LegalDescr'],0,0,'C');
          $this->Ln(10);
          $this->Cell(80);
          $this->Cell(30,10, $GLOBALS['Municipality'],0,0,'C');
          $this->Cell(50);
          $this->Cell(0,-30, " #".$GLOBALS['cemCode']);
          $this->Ln(20);  

          //If the page number is not the last page print the section number and row number.
          //NOTE: PageNo refers to the page number of the pdf as a whole, Ex.) PageNo(1) is the title page. numPageNo refers to the page number in the footer of the pdf, Ex.) numPageNo(1) is the first cemetery transcription page because that is where the page numbering starts.
          if ($this->numPageNo() != $GLOBALS['lastPage']) 
          {
            $this->printSectRow(); 
          }

        }
        //Otherwise display the title page.
        else if($this->PageNo() == 1)
        {
          /*=================================== Title Page Stuff ================================================*/
          //"Header" of the title page.
          $this->SetFont('Times', '', 14);
          //This image path may need to change depending on where the MGS logo is stored.
          $this->Image("../img/mgs-square.png",15,15,35,35);
          $this->Multicell(0, 5);
          $this->Cell(45);
          $this->MultiCell(0, 5, "Manitoba Genealogical Society",0,'C');
          $this->Cell(45);
          $this->SetFont('Times', '', 12);
          $this->MultiCell(0, 5, "Unit E-1045 St James St., Winnipeg, Manitoba Canada R3H 1B1",0,'C');
          $this->Cell(45);
          $this->MultiCell(0, 5, "Phone (204)-783-9139 email contact@mbgenealogy.com",0,'C');
          $this->Cell(45);
          $this->MultiCell(0, 5, "website www.mbgenealogy.com",0,'C');
          $this->SetFont('Times','B',15);
          $this->Multicell(0, 50, "Cemetery Transcription",0,'C');
          $this->SetFont('Times', '', 14);

          //Formats the body of the title page depending on how long the cemetery name is.
          //Longer cemetery names push the body text to the left in order to include the entire name on one line.
          if (strlen($GLOBALS['CemName']. "    #".$GLOBALS['cemCode']) >= 50) 
          {
            $this->Cell(1);
            $this->Cell(50, 0, "Cemetery Name: ");
            $this->Cell(-50,0, $GLOBALS['CemName']. "    #".$GLOBALS['cemCode']);
            $this->Cell(50, 25, "Location: ");
            $this->Cell(-50, 25, $GLOBALS['LegalDescr']);
            $this->Cell(50, 50, "City/Municipality: ");
            $this->Cell(-50, 50, $GLOBALS['Municipality']);
            $this->Cell(50, 75, "Date Of Reading: ");
            $this->Cell(-50, 75, $GLOBALS['DateRead']);
            $this->Cell(50, 100, "Transcribers: ");
            $this->Cell(-50, 100, $GLOBALS['Transcribers']);
            $this->Cell(50, 125, "Data Input By: ");
            $this->Cell(-50, 125, $GLOBALS['DataInput']);
            $this->Cell(50, 150, "Lat/Long: ");
            $this->Cell(-50, 150, $GLOBALS['GPSLat']. " ". $GLOBALS['GPSLong']);
          }
          //If the legal description is longer than 50 characters format the legal description as a multi cell (multi-line).
          else if (strlen($GLOBALS['LegalDescr']) >= 50) 
          {
            $this->Cell(1);
            $this->Cell(50, 0, "Cemetery Name: ");
            $this->Cell(-50,0, $GLOBALS['CemName']. "    #".$GLOBALS['cemCode']);
            $this->Multicell(1, 15);
            $this->Cell(50, 5, "Location: ");
            $this->Multicell(120, 5, $GLOBALS['LegalDescr']);
            $this->Multicell(1, -15);
            $this->Cell(50, 50, "City/Municipality: ");
            $this->Cell(-50, 50, $GLOBALS['Municipality']);
            $this->Cell(50, 75, "Date Of Reading: ");
            $this->Cell(-50, 75, $GLOBALS['DateRead']);
            $this->Cell(50, 100, "Transcribers: ");
            $this->Cell(-50, 100, $GLOBALS['Transcribers']);
            $this->Cell(50, 125, "Data Input By: ");
            $this->Cell(-50, 125, $GLOBALS['DataInput']);
            $this->Cell(50, 150, "Lat/Long: ");
            $this->Cell(-50, 150, $GLOBALS['GPSLat']. " ". $GLOBALS['GPSLong']);
          }
          //Otherwise format the index page normally.
          else
          {
            $this->Cell(10);
            $this->Cell(65, 0, "Cemetery Name: ");
            $this->Cell(-65,0, $GLOBALS['CemName']. "    #".$GLOBALS['cemCode']);
            $this->Cell(65, 25, "Location: ");
            $this->Cell(-65, 25, $GLOBALS['LegalDescr']);
            $this->Cell(65, 50, "City/Municipality: ");
            $this->Cell(-65, 50, $GLOBALS['Municipality']);
            $this->Cell(65, 75, "Date Of Reading: ");
            $this->Cell(-65, 75, $GLOBALS['DateRead']);
            $this->Cell(65, 100, "Transcribers: ");
            $this->Cell(-65, 100, $GLOBALS['Transcribers']);
            $this->Cell(65, 125, "Data Input By: ");
            $this->Cell(-65, 125, $GLOBALS['DataInput']);
            $this->Cell(65, 150, "Lat/Long: ");
            $this->Cell(-65, 150, $GLOBALS['GPSLat']. " ". $GLOBALS['GPSLong']);
          }

          //"Footer" of the title page.
          $this->Ln(130);
          $this->SetFont('Times', 'I', 11);
          $this->Cell(20);
          $this->MultiCell(150,5,"This transcription has been produced by the Special Project Committee of the Manitoba Genealogical Society Inc. Every effor has been made to ensure accuracy, however, M.G.S does not accept responsibility for errors found in the transcription.",0,'C');
          $this->Ln(5);
          $this->Cell(20);
          $this->MultiCell(150,5,"Extracts or photocopies of several pages pertaining to a researcher's family are permitted. However, this transcription MAY NOT be reproduced IN TOTAL, in any form (e.g , Internet or local history book) without permission of the Special Projects Chair of M.G.S",0,'C');

          $this->AddPage();
          /*===================================== End Of Title Page ==================================================*/
          /*=================================== Historic Write Up Page ================================================*/

          //Creates an array of historic write up paragraphs.
          //A new array index is created after each instance of a double space (  ).
          $historicWriteUp = explode('  ', $GLOBALS['HistoricWriteUp']);

          //Formats the historic write up page.
          $this->SetFont('Times', 'BU', 18);
          $this->Ln(5);
          $this->Multicell(0,0,$GLOBALS['CemName']. " Cemetery ",0,'C');
          $this->Multicell(0,20,$GLOBALS['Municipality']. " Manitoba",0,'C');
          $this->SetFont('Times', '', 12);
          $this->Ln(10);

          //Loops throug hthe historic write up array and outputs each index.
          for ($i=0; $i < sizeof($historicWriteUp) ; $i++) 
          { 
            $this->Multicell(180,5,"".$historicWriteUp[$i]);
            $this->Ln(3);
          }

          $this->AddPage();
          /*=================================== End Of Historic Write Up Page =========================================*/
        }
      }

      /*
       * This function is used to print out the section and row numbers. 
       */
      function printSectRow()
      {
          $this->SetFont('Times', 'BU', 12);
          $this->Cell(0, 0, $GLOBALS['section']);
          $this->Ln(10);
          $this->Cell(0, 0, $GLOBALS['row']);
          $this->Ln(5);
      }
    }

    /*
     * Creates a formatted report of all cemetery transcriptions for the specified cemetery.
     *
     * $cemeteryName: The name of the specified cemetery.
     * $cemCode: The unique number that relates to the specified cemetery.
     */
    function createCemeteryReport(string $cemeteryName, int $cemCode)
    {
      //Set global variables to be used in the header function.
      $GLOBALS['cemName'] = $cemeteryName;
      $GLOBALS['cemCode'] = $cemCode;
      $GLOBALS['section'] = '';
      $GLOBALS['row'] = '';

      require('../db/adminCheck.php');
      require('../db/mgsConnection.php');

      //Selects necessary cemetery information from the cemeteries table.
      $cemeterySql = "SELECT CemDescr, LegalDescr, Municipality, GPS_Lat, GPS_Long, CemCode, Transcriber, DataInput, DateRead, Historic_Write_Up FROM Cemeteries WHERE CemCode = $cemCode";

      $cemeteryStmt = sqlsrv_query($conn, $cemeterySql);

      //Selects necessary cemetery transcription data from the cemetery transcriptions table.
      //+ 0 ensures plot numbers are ordered as numbers, not strings.
      $ctSql = "SELECT Section, Row, Plot, Surname, MiddleName, GivenName, Inscription, TypeOfStone, Notes FROM CemeteryTranscriptions WHERE CemeteryCode = $cemCode ORDER BY Section, Row, Plot ASC";

      $ctStmt = sqlsrv_query($conn, $ctSql);

      print_r(sqlsrv_errors());
      
      //If the cemetery statement or the cemetery transcription statement fails redirect to the tablesDashboard page.
      if ($cemeteryStmt === false || $ctStmt === false) 
      {
        header("Location: tablesDashboard.php");
      }
      else
      {
        //This array will hold cemetery transcription data.
        $ctData = array();

        $cemeteryRow = sqlsrv_fetch_array($cemeteryStmt);

        //Set GLOBAL variables to be used in the header and footer functions.
        $GLOBALS['LegalDescr'] = $cemeteryRow['LegalDescr'];
        $GLOBALS['Municipality'] = $cemeteryRow['Municipality'];
        $GLOBALS['CemName'] = $cemeteryRow['CemDescr'];
        $GLOBALS['DateRead'] = $cemeteryRow['DateRead'];
        $GLOBALS['Transcriber'] = $cemeteryRow['Transcriber'];
        $GLOBALS['DataInput'] = $cemeteryRow['DataInput'];
        $GLOBALS['GPSLat'] = $cemeteryRow['GPS_Lat'];
        $GLOBALS['GPSLong'] = $cemeteryRow['GPS_Long'];
        $GLOBALS['HistoricWriteUp'] = $cemeteryRow['Historic_Write_Up'];

        //Create a new pdf document, set the authors, font, font size and start page numbering.
        $pdf = new PDF();
        $pdf->SetCreator("Jade Buhler, Matthew Isliefson");
        $pdf->AddPage();
        $pdf->SetFont('Times','B',12);
        $pdf->startPageNums();

        //DO NOT DELETE THIS BLOCK OF CODE.
        //While rows are being fetched from the cemetery transcription table place those rows in the ctData array. 
        //This creates a 2d array.
        while ($ctRow = sqlsrv_fetch_array($ctStmt, SQLSRV_FETCH_ASSOC)) 
        {
          $ctData[] = $ctRow;
        }

        //Loops through each array in the ctData array.
        for ($i=0; $i < sizeof($ctData); $i++) 
        { 
          //If the current section does not match the previous section print the section heading.
          if ($ctData[$i]['Section'] != $ctData[$i-1]['Section']) 
          {
            $GLOBALS['section'] = "Section: ".$ctData[$i]['Section'];
            $pdf->SetFont('Times', 'BU');
            $pdf->Multicell(0, 5, "Section: ".$ctData[$i]['Section']);
            $pdf->Ln(3);
          }

          //If the current row does not match the prevous row print the row heading.
          if ($ctData[$i]['Row'] != $ctData[$i-1]['Row']) 
          {
            $GLOBALS['row'] = "Row ".$ctData[$i]['Row'];
            $pdf->SetFont('Times', 'BU');
            $pdf->Multicell(0, 7, "Row ".$ctData[$i]['Row']);
          }

          //Prints the plot, surname and inscription fields with the proper formatted spacing.
          $pdf->SetFont("Times");
          $pdf->Cell(1);

          if (strlen($ctData[$i]['TypeOfStone']) >= 25)  
          {
            $pdf->Cell(20, 5, "Plot ".$ctData[$i]['Plot']);
            $pdf->Multicell(20, 5, $ctData[$i]['TypeOfStone']);
            $pdf->Multicell(1, -15);
            $pdf->Cell(58, 1);
            $pdf->Cell(30, 5, $ctData[$i]['Surname']);

            //Adds the surnames, given name and middle name to the table of contents.
            $pdf->TOC_Entry($ctData[$i]['Surname'].", ".$ctData[$i]['GivenName']. " ".$ctData[$i]['MiddleName'], 1);
            $pdf->Multicell(100, 5, $ctData[$i]['Inscription']);
            $pdf->Ln(10);
          }
          else
          {
            $pdf->Cell(25, 5, "Plot ".$ctData[$i]['Plot']);
            $pdf->Cell(32, 5, $ctData[$i]['TypeOfStone']);
            $pdf->Cell(30, 5, $ctData[$i]['Surname']);

            //Adds the surnames, given name and middle name to the table of contents.
            $pdf->TOC_Entry($ctData[$i]['Surname'].", ".$ctData[$i]['GivenName']. " ".$ctData[$i]['MiddleName'], 1);
            $pdf->Multicell(100, 5, $ctData[$i]['Inscription']);
            $pdf->Ln(10);
          }

          //If the current notes field is not null or an empty string print the notes with proper formatted spacing.
          if ($ctData[$i]['Notes'] != null || $ctData[$i]['Notes'] != "") 
          {
            $pdf->Ln(-5);
            if (strlen($ctData[$i]['TypeOfStone']) >= 25)  
            {
              $pdf->Cell(88);
            }
            else
            {
              $pdf->Cell(78);
            }
            $pdf->Multicell(0, 0, $ctData[$i]['Notes']);
            $pdf->Ln(5);
          }

          //Gets the current Y position of the page. 
          //This is used to ensure there is room for the footer on each page.
          $y = $pdf->GetY();
    
          //If the position of Y is greater than or equal to 280 create a new page and reset $y to 0.
          if ($y+$pdf->line_height >= 260 ) 
          {
            $pdf->AddPage();
            $y = 0;
          }
        }
        $GLOBALS['lastPage'] = $pdf->numPageNo();
      }

      //Inserts a table of contents on page 3 of the PDF.
      $pdf->insertTOC(3);

      //This line is needed to output the pdf.
      $pdf->Output();
    }
?>
