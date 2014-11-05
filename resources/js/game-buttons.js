$('.btn-start').click(function(){
    $.post("/start-game", {}, function(data) {
        if (data.code == 200) {
            changeStatus($('.btn-start'), false);
            changeStatus($('.btn-stop'), true);
            changeStatus($('.btn-restart'), true);
        }
    });
});
$('.btn-stop').click(function(){
    $.post("/stop-game", {}, function(data) {
        if (data.code == 200) {
            changeStatus($('.btn-start'), true);
            changeStatus($('.btn-stop'), false);
            changeStatus($('.btn-restart'), false);
        }
    });
});
$('.btn-restart').click(function(){
    $.post("/restart-game", {}, function(data) {
        if (data.code == 200) {
            changeStatus($('.btn-start'), false);
        }
    });
});
$('.btn-leave').click(function(){
    $.post("/leave-game", {}, function(data) {
        if (data.code == 200) {
            window.location = "/rooms";
        }
    });
});

function changeStatus(button, status) {
    status ? button.removeAttr('disabled') : button.attr("disabled", "disabled");
}
