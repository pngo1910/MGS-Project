
   $("document").ready(function()
   {
        $("#btn1").click(function(e)
        {
          var checkboxes = $(".all");
           checkboxes.prop('checked',true) ;

          
        })
       $("#btn2").click(function()
       {

            var checkboxes = $(".all");
            checkboxes.prop('checked',false) ;

        })

   });
  
