<script type="text/javascript">
	var asInitVals = new Array();
	var j_cols = new Array();
	<?php foreach ($aCol as $key => $value) : ?>
		j_cols.push({'sTitle' : '<?= $value ?>'});       		
	<?php endforeach; ?>

    $(document).ready(function() {
        window.alert = function(){return null;};
        var calcDataTableHeight = function() {
                return $(window).height()*55/80;
            }; 
        var oTable = $('#example').dataTable( {
        	"scrollY": calcDataTableHeight(),
            "scrollCollapse": true,
            "bProcessing": true,
            "bPaginate": true, 
            "bServerSide": true,                 
            "bsortClasses": false,              
            "sPaginationType": 'full_numbers',
			"aLengthMenu": [ 10, 25, 50, 100, 500 ],
            "bFilter": true,
            "bInput" : true,
            "aoColumns": j_cols,
            "sAjaxSource": "storeQuery.php?name=<?= $name ?><?= $municipality ?>",	
            "oLanguage": {
                "sSearch": "Search all columns:"
            },
            "fnDrawCallback": function () {
        		//Call the onload function
        		onload();

				//Check if saveCart has a value
				if(saveCart != "")
				{
					//Call addQuantities()
					addQuantities();
				}
				//Clear the message
                document.getElementById("message").innerHTML = "";
          	},
          	"fnInitComplete": function() {
                $('.dataTables_scrollFoot').insertAfter($('.dataTables_scrollHead'));
            }
        } );

        $("tfoot input").keyup( function () {
             //Filter on the column (the index) of this element 
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

        //Remove the placeholder when it's focused on then bring it back on blur
        $('[placeholder]').each(function(){
		    var $this = $(this);
		    $this.data('placeholder', $this.attr('placeholder'))
		         .focus(function(){$this.removeAttr('placeholder');})
		         .blur(function(){$this.attr('placeholder', $this.data('placeholder'));});
		});
    } );
	
	/*
	*	If the user isn't near the top of the page, this will make the message appear at the top of the window and scroll with it until it fades away
	*/
	function stickyMessage(){
		var stickyMessageTop = $('#message').offset().top;
        var scrollTop = $(window).scrollTop();
        if (scrollTop > stickyMessageTop || scrollTop == stickyMessageTop && stickyMessageTop != 0) { 
        	$('#message').removeClass('message');
            $('#message').addClass('messagebox');
            if($('#message').css('display') == 'none'){
            	$('#message').css('display', 'inline');
            }
        } else {
            $('#message').removeClass('messagebox');
            $('#message').addClass('message');
            if($('#message').css('display') == 'none'){
            	$('#message').css('display', 'inline');
            }
        }
	}

	//Create an array to hold the user's chosen products and quantities
	//It's created here so it doesn't empty the cart every time the user adds a product
	var cart = [];
	/*
	*	This function adds the chosen products to the shopping cart as long as they're not duplicates.
	*	It also updates the quantities of products that have already been added to the cart only if the
	*	number has changed.
	*/
	function addToShoppingCart(){
		//Remove all the empty elements and add the non-empty values to a new array
		var filteredCart = $.grep(saveCart,function(n){
	        return(n);
	    });
		//This is used to test for duplicate ids
		var isEqual = false;
		//This is to test if a product was removed from the cart
		if(filteredCart.length < cart.length)
		{
			//Empty the shopping cart
			cart = [];
			//Loop through the saveCart
			for(var i = 0; i < filteredCart.length; i++)
			{
				//Set the id
				var id = filteredCart[i];
				//Increment to the quantity
				i++;
				//Add the new value(s) to the shopping cart
				cart.push(id, filteredCart[i]);
			}
		}
		else
		{
			//Loop through the values in the saveCart array
			for(var i = 0; i < filteredCart.length; i++)
			{
				if(filteredCart[i].value != 0)
				{
					//Loop through the values in the shopping cart array
					for(var j = 0; j < cart.length; j++)
					{
						//Check if the id in the saveCart equals the id in the shopping cart
						if(filteredCart[i] == cart[j])
						{
							//Set the id
							var id = filteredCart[i];
							//Increment by one so that the next iteration of the loop skips the quantity
							j++;
							//Increment by one to get the quantity
							i++;
							//Add the quantity to the cart (even if the quantity didn't change)
							cart[j] = filteredCart[i];
							//Decrease by one to go back to the id
							i--;
							//Set the flag to true
							isEqual = true;
							//Exit the loop
							j = cart.length;
						}
						else
						{
							//Set the flag to false
							isEqual = false;
							//Increment by one to skip the quantity for the next iteration of the loop
							j++;
						}
					}

					//Check if isEqual is false
					if(!isEqual)
					{
						//Set the id
						var id = filteredCart[i];
						//Increment to get the quantity
						i++;
						//Push the id and quantity to the shopping cart
						cart.push(id, filteredCart[i]);
					}
					else
					{
						//Increment by one to skip the quantity for the next iteration of the loop
						i++;
					}
				}
			}
		}

		//Check if the shopping cart or filteredCart is empty
		if(cart.length == 0 || filteredCart.length == 0)
		{
			//If filteredCart is empty but the shopping cart isn't, empty it
			cart = [];
			//Give a message to the user
			stickyMessage();
			$('#message').html("You must choose at least one product to add to your shopping cart.").delay(3000).fadeOut();
			$('#message').css({'color': 'red', 'font-weight': 'bold'});
		}
		else
		{
			//Set the hidden input value to the values in the cart to be used in the purchase form
			document.getElementById('values').value = cart;
			//Give a message to the user
			stickyMessage();
			$('#message').html("The products you have chosen have been successfully added to your shopping cart.").delay(3000).fadeOut();
			$('#message').css({'color': 'green', 'font-weight': 'bold'});
		}

		/*
		* This is for the Items in Cart
		*/
		var total = 0;

		//Loop through the cart
		for(var i = 0; i < filteredCart.length; i++){
			i++;
			//Add the quantities
			total = total + parseInt(filteredCart[i]);
		}

		//Notice there are two getElementById's; one values and one finalvalues
		//values is to temporarily hold the quantities as they change while finalvalues is what will be used on the shoppingCart page
		<?php if(!empty($_SESSION['values'])): ?>
			if(total == 0){
				document.getElementById("finalvalues").value = "<?= $holdvalues ?>";
				document.getElementById("cart").innerHTML = <?= $total ?>;
			}else if(total == <?= $total ?>){
				document.getElementById("finalvalues").value = document.getElementById("values").value;
				document.getElementById("cart").innerHTML = <?= $total ?>;
			}else if(total < <?= $total ?> || total > <?= $total ?>){
				//Set the cart text to the total
				document.getElementById("cart").innerHTML = total;
				document.getElementById("finalvalues").value = document.getElementById("values").value;
			}
		<?php else: ?>
			//Set the cart text to the total
			document.getElementById("cart").innerHTML = total;
			document.getElementById("finalvalues").value = document.getElementById("values").value;
		<?php endif; ?>
	}

	/*
	*	Checks that the cart has something in it before allowing the user to view their shopping cart.
	*	Returns true or false to allow or stop the form from being submitted.
	*/
	function toCart(){
		//A flag to test if there is a value in the cart
		var hasValue = false;
		if(cart.length != 0)
		{
			//Set hasValue to true
			hasValue = true;
		}
		//Check that the session has a value
		//(Even though the cart might be empty, the user could still have products in the session)
		<?php if (!empty($_SESSION['values'])): ?>
			//Set hasValue to true
			hasValue = true;
		<?php endif; ?>

		if(hasValue)
		{
			//There's at least one value in the cart so return true
			return true;
		}
		else
		{
			//Give a message to the user
			stickyMessage();
			$('#message').html("You must have a product in your shopping cart before viewing it.").delay(3000).fadeOut();
			$('#message').css({'color': 'red', 'font-weight': 'bold'});
			//Stop the form from submitting
			return false;
		}
	}

	/*
	*	This function sets the hidden input 'values' value to the values in the cart so that way when the user
	*	chooses to view their shopping cart again without adding a new quantity or product to their cart the
	*	$_POST['values'] variable won't be just an empty string. If that happened the user would lose all the
	*	products in their shopping cart.
	*	It also sets the add product inputs to the users chosen quantities for each product.
	*/
	function onload()
	{
		//Check that the session isn't empty
		<?php if(!empty($_SESSION['values'])): ?>
			document.getElementById("finalvalues").value = "<?= $holdvalues ?>";
			//Get the value that's in the hidden input 'values'
			var changedValues = document.getElementById('values').value,
				//Get all the quantity inputs
				inputs = document.getElementsByClassName("products"),
				//Create an array to hold the values that will be the hidden input 'values' new value
				values = [];
			/* This is used to determine if the user has just returned from viewing their shopping cart
			* If they have then the hidden input 'values' is empty so we loop through the session to populate
			* the 'values' input.
			* If the user has gone to another page in the store, the loop going through the changedValues is going
			* to check if the user has changed the quantities on any of the products that are already in the shopping
			* cart and update the 'values' input with the new values.
			*/
			//Check if changedValues is empty
			if(changedValues == "")
			{
				//Loop through all the values in the session
				<?php for($i = 0; $i < count($_SESSION['values']); $i++): ?>
					//Set the id variable
					var id = <?= $_SESSION['values'][$i] ?>;
					//Increment by one to get the quantity
					<?php $i++; ?>
					//Set the quantity variable
					var quantity = <?= $_SESSION['values'][$i] ?>;
					//Push the id and quantity to the values array
					values.push(id, quantity);
				<?php endfor; ?>
			}
			else
			{
				//Split the values by comma delimiter to create an array of product ids and quantities
				changedValues = changedValues.split(',');
				//Loop through the changedValues variable
				for(var i = 0; i < changedValues.length; i++)
				{
					//Set the ids variable
					var ids = changedValues[i];
					//Increment by one to get the quantity
					i++;
					//Push the id and quantity to the values array
					values.push(ids, changedValues[i]);
				}
			}

			//Loop through the add product inputs
			for(var i = 0; i < inputs.length; i++)
			{
				//Loop through the values in the session
				<?php for($i = 0; $i < count($_SESSION['values']); $i++): ?>
					//Set the id
					var id = <?= $_SESSION['values'][$i] ?>;
					//Increment by one to get the quantity
					<?php $i++; ?>
					//Set the quantity
					var quantity = <?= $_SESSION['values'][$i] ?>;

					//Get the id of the input and check if it equals the id in the session
					if(inputs[i].getAttribute('id') == id)
					{
						//Set the input value to the quantity
						inputs[i].value = quantity;
					}
				<?php endfor; ?>
			}

			/*
			* This is for the Items in Cart
			*/
			<?php $total = 0; ?>
			//Loop through the session
			<?php for($i = 0; $i < count($_SESSION['values']); $i++): ?>
				<?php $i++; ?>
				//Add the quantities
				<?php $total += $_SESSION['values'][$i]; ?>
			<?php endfor; ?>
			
			//Check if the total in session doesn't equal the text in the cart and that the text isn't 0
			if(<?= $total ?> != parseInt(document.getElementById('cart').innerHTML) && parseInt(document.getElementById('cart').innerHTML) != 0 ){
				//Set the text in cart to itself
				document.getElementById('cart').innerHTML = parseInt(document.getElementById('cart').innerHTML);
			} else {
				//Set the text of cart to the total
				document.getElementById('cart').innerHTML = <?= $total ?>;
			}

			//Set the input 'values' value to the values array
			document.getElementById('values').value = values;
		<?php endif; ?>
	}

	//Create a new array
	var saveCart = [];
	var filtered = null;
	/*
	*	This function keeps track of all the products the user has added a quantity to so that way they
	* 	can add all the products they chose to the shopping cart at once no matter what page they're on.
	*/
	function holdProducts(id, quantity){
		<?php if(isset($holdvalues)): ?>
			document.getElementById("finalvalues").value = "<?= $holdvalues ?>";
		<?php endif; ?>
		//This variable will be used to hold the values in the hidden input
		var inputValues = "",
			//A flag to test if there is match between the shopping cart and hidden input
			isEqual = false;
		//Check if the hidden input has a value
		if(document.getElementById('values').value != "")
		{
			//Split the values and assign them as an array to inputValues
			inputValues = document.getElementById('values').value.split(",");
			//Loop through the values
			for(var i = 0; i < inputValues.length; i++)
			{
				//Loop through the shopping cart
				for(var j = 0; j < saveCart.length; j++)
				{
					//Check if the shopping cart ids equal the input ids
					if(saveCart[j] == inputValues[i])
					{
						//Set the flag to true
						isEqual = true;
						//Exit the loop
						j = saveCart.length;
					}
					else
					{
						//Increment by one to skip the quantity on the next iteration of the loop
						j++;
					}
				}

				//Check if no match was found
				if(!isEqual)
				{
					//Set the id
					var ids = inputValues[i];
					//Increment to get the quantity
					i++;
					//Push the id and quantity to saveCart
					saveCart.push(ids, inputValues[i]);
				} else {
					//Increment to skip the quantity
					i++;
				}
			}
		}
		//A flag used to check if the id in saveCart already exists
		var match = false;
		//Check that there's a value in saveCart
		if(saveCart.length == 0)
		{
			//Push the id and quantity to the cart
			saveCart.push(id, quantity);
		}
		else
		{
			//Loop through the saveCart array
			for(var i = 0; i < saveCart.length; i++)
			{
				//If the sent id equals the id in saveCart
				if(id == saveCart[i])
				{
					//Increment to the quantity so the next iteration of the loop increments to the id
					i++;
					//Update the quantity for the product in the array
					saveCart[i] = quantity;
					//Set the flag to true
					match = true;

					if(quantity == 0)
					{
						//Go back one to the id
						i--;
						//Remove the id
						saveCart.splice(i, 1);
						//Remove the quantity
						saveCart.splice(i, 1);
					}

					//Exit the loop
					i = saveCart.length;
				}
				else
				{
					//Increment to the quantity so the next iteration of the loop increments to the id
					i++;
				}
			}

			//Check if no match was found
			if(!match)
			{
				//Add the id and quantity to the array
				saveCart.push(id, quantity);
			}
		}
		
		//Remove all the empty elements and add the non-empty values to a new array
	    filtered = $.grep(saveCart,function(n){
	        return(n);
	    });
	    
		document.getElementById("values").value = filtered;
	}

	/*
	*	When a user adds quantities to the products, goes to a different page and goes back,
	*	this function re-adds the quantities chosen to the text fields so the user doesn't have
	*	to put them in again.
	*/
	function addQuantities(){
		//Loop through saveCart
		for(var i = 0; i < saveCart.length; i++)
		{
			//Get the input with the id in saveCart
			var input = document.getElementById(saveCart[i]);
			//Check that input isn't null
			if(input != null)
			{
				//Increment to get the quantity
				i++;
				//Set the input value to the quantity in saveCart
				input.value = saveCart[i]; 
			}
			//Input is null
			else
			{
				//Increment by one to skip the quantity on the next iteration of the loop
				i++;
			}
		}
	}
</script>

