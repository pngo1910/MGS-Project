#!/bin/bash
gs -sDEVICE=pdfwrite -dNOPAUSE -dBATCH -dSAFER -dFirstPage=${1} -dLastPage=${2} -sOutputFile=C:/xampp/htdocs/mani/PDF/${3}