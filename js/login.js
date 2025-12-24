
function trylogin()
{
    let un=$("#txtusername").val();
    let pw=$("#txtpassword").val();
    // proceed only when both fields are filled
    if(un.trim()!=="" && pw.trim()!="")
    {
        //make an ajax call
        $.ajax({
            url:"ajaxhandler/loginAjax.php",
            type:"POST",
            dataType:"json",
            data:{user_name:un,password:pw,action:"verifyUser"},
            beforeSend:function(){
                //if you want to do something just before making the call
                //console.log("about to make an ajax call");
                // alert("Logging in... Please wait");
            },
            success:function(rv){
                // rv is already parsed as JSON when dataType is "json"
                // alert(JSON.stringify(rv));
                if(rv.status=="success")
                {
                    document.location.replace("attendance.php");
                }
                else
                {
                    alert("Login failed: "+rv.message);
                }
            },
            error:function(){
                //if for some reason the call was unsuccessfull
                alert("An error occurred while making the ajax call");
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
    $(document).on("click",".btnlogin",function(e){
        e.preventDefault();
        trylogin();
    });
});









$(function(e){
    //capture the keyup event
    $(document).on("keyup","input",function(e){
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