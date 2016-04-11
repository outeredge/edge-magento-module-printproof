document.observe('dom:loaded', function(){

    var myswitch = document.getElementById("default_reply");
    var mytextarea = document.getElementById("history_comment");
    myswitch.onchange = function () {
        if (this.checked) {
            mytextarea.value = myswitch.value;
        } else {
            mytextarea.value = "";
        }
    }

});