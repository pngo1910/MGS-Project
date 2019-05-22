<script type="text/javascript">
    //Create a variable to hold the name of the button that's pressed (update or remove)
    var submit;
    /*
    *   This function updates the quantities and removes the products.
    *   Returns true or false to allow or stop the form from submitting
    */
    function updatePage(submit){
        if(submit == "Update Quantity") {
            //Get all the quantities
            var quantity = document.getElementsByClassName("quantity"),
                //Create an array to hold the quantities
                quantities = [];
            //Loop through the quantities
            for(var i = 0; i < quantity.length; i++) {
                //Push the values into the array
                quantities.push(quantity[i].value);
            }
            //Set the hidden input 'quantities' value to the array
            document.getElementById("quantities").setAttribute("value", quantities);
            //Return true to let the form submit
            return true;
        }
        //Remove Products was chosen
        else {
            //Get the hidden input 'ids'
            var ids = document.getElementById("ids"),
                //Get all the checkboxes
                checkboxes = document.getElementsByClassName("checkbox"),
                //Create an array to hold the producst
                products = [],
                //Create an index for the products array
                index = 0,
                //Create a flag to determine if at least one checkbox is checked
                checked = false;
            //Loop through the checkboxes
            for(var i = 0; i < checkboxes.length; i++) {
                //Check if the checkbox is checked
                if(checkboxes[i].checked) {
                    //Add the value of the checked checkbox to the array
                    products[index] = checkboxes[i].value;
                    //Increment the index
                    index++;
                    //Set the flag to true
                    checked = true;
                }
            }

            //Check if no checkbox was checked
            if(!checked) {
                //Give a message to the user
                document.getElementById("message").innerHTML = "You must select at least one product to remove.";
                document.getElementById("message").setAttribute("class", "errorColor");
                //Prevent the form from submitting
                return false;
            }
            
            //Let the user confirm that they really want to remove the chosen products from their cart
            if (confirm('Remove chosen product(s)?')) {
                //Set the hidden input 'ids' value to the products array
                ids.setAttribute("value", products);
                //Allow the form to submit
                return true;
            }
            else {
                //Prevent the form from submitting
                return false;
            }
        }
    }

    /*
    *   Let the user confirm if they would like to continue to PayPl
    *   returns true if the user accepts the confirmation, or false if they decline
    */
    function purchaseProducts()
    {
        if(confirm("Are you ready to proceed to PayPal?")){
            return true;
        }
        return false;
    }
</script>