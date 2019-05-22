<SCRIPT LANGUAGE="JavaScript">
   $("document").ready(function()
   {
        $("#btn1").click(function()
        {
            $("[name='checkbox']").attr("checked",'true');//全选
        })
       $("#btn2").click(function()
       {
            $("[name='checkbox']").removeAttr("checked");//取消全选
        })

   })
  </SCRIPT>