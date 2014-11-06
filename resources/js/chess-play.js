var onDrop = function(source, target) {
    var valid_move = false;

    if (source == target) {
        return 'snapback';
    }

    var color = 'white';
    $.ajax({
        type: 'POST',
        url: "/move-piece",
        data: {
            move: source + ' ' + target + ' ' + 'Q'
        },
        success: function(data) {
            if (data.code == '200') {
                valid_move = true;
                color = data.color;
                chess_turn = data.turn;
            }
        },
        dataType: 'json',
        async:false
    });

    // illegal move.
    if (!valid_move) return 'snapback';

    // Highlight the move.
    removeHighlights(color);
    highlight(source, color);
    highlight(target, color);

    updateStatus();
};

var cfg = {
    draggable: true,
    position: chess_start_position,
    orientation: chess_orientation,
    onDrop: onDrop,
    pieceTheme: pieceTheme
};
board = new ChessBoard('board', cfg);

updateStatus();
prepareHighlights();
