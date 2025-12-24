//do everything only when document is loaded
$(function(e){
    //capture the keyup event
    $(document).on("keyup","input",function(e){
        let un=$("#txtusername").val();
        let pw=$("#txtpassword").val();
        if(un.trim()!="" && pw.trim()!="")
        {
            $("#btnlogin").removeClass("inactivecolor");
            $("#btnlogin").addClass("activecolor");
        }
        else
        {
            $("#btnlogin").addClass("inactivecolor");
            $("#btnlogin").removeClass("activecolor");
        }
    });
});