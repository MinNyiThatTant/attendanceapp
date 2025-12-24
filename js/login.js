
function trylogin()
{
    let un=$("#txtusername").val();
    let pw=$("#txtpassword").val();
    // proceed only when both fields are filled
    if(un.trim()!=="" && pw.trim()!="")
    {
        //make an ajax call
        // ensure lockscreen shows at least this many ms so user can read message
            const minLockMs = 700;
        let lockStart = 0;

        $.ajax({
            url:"ajaxhandler/loginAjax.php",
            type:"POST",
            dataType:"json",
            data:{user_name:un,password:pw,action:"verifyUser"},
            beforeSend:function(){
                //if you want to do something just before making the call
                //console.log("about to make an ajax call");
                // alert("Logging in... Please wait");
                $("#diverror").removeClass("applyerrordiv");
                $("#lockscreen").addClass("applylockscreen");
                lockStart = Date.now();
            },
            success:function(rv){
                // rv is already parsed as JSON when dataType is "json"
                const elapsed = Date.now() - lockStart;
                const wait = Math.max(0, minLockMs - elapsed);
                setTimeout(function(){
                    $("#lockscreen").removeClass("applylockscreen");
                    if(rv.status=="success")
                    {
                        document.location.replace("dashboard.php");
                    }
                    else
                    {
                        $("#diverror").addClass("applyerrordiv");
                        $("#errormessage").text(rv['message']);
                    }
                }, wait);
            },
            error:function(){
                //if for some reason the call was unsuccessfull
                const elapsed = Date.now() - lockStart;
                const wait = Math.max(0, minLockMs - elapsed);
                setTimeout(function(){
                    $("#lockscreen").removeClass("applylockscreen");
                    alert("An error occurred while making the ajax call");
                }, wait);
            }
        });
    } else {
        alert("Please enter username and password");
    }
}
//do everything only when document is loaded
$(function(e){
    $(document).on("keyup","input",function(e){
    });
    $(document).on("click","#btnlogin",function(e){
        e.preventDefault();
        trylogin();
    });
});


$(function(e){
    //capture the keyup event
    $(document).on("keyup","input",function(e){
        $("#diverror").removeClass("applyerrordiv");
        // $("errormessage").text("");
        let un=$("#txtusername").val();
        let pw=$("#txtpassword").val();
        if(un.trim()!="" && pw.trim()!="")
        {
            $(".btnlogin").removeClass("inactivecolor");
            $(".btnlogin").addClass("activecolor");
        }
        else
        {
            $(".btnlogin").addClass("inactivecolor");
            $(".btnlogin").removeClass("activecolor");
        }
    });
});