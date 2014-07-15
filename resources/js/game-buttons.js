$('.btn-start').click(function(){
    $.post("/start-game", {}, function(data) {
        if (data.message == 'Game started' && data.code == 200) {
            $('.btn-start').addClass('disabled');
            $('.btn-stop').removeClass('disabled');
            $('.btn-restart').removeClass('disabled');
        }
    });
});
$('.btn-stop').click(function(){
    $.post("/stop-game", {}, function(data) {
        if (data.message == 'Game stopped' && data.code == 200) {
            $('.btn-stop').addClass('disabled');
            $('.btn-start').removeClass('disabled');
            $('.btn-restart').addClass('disabled');
        }
    });
});
$('.btn-restart').click(function(){
    $.post("/restart-game", {}, function(data) {
        if (data.message == 'Game re-started' && data.code == 200) {
            $('.btn-start').addClass('disabled');
        }
    });
});
