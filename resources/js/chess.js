var board,
boardEl = $('#board'),
statusEl = $('#status'),
squareClass = 'square-55d63'
;

var onDrop = function(source, target) {
    var valid_move = false;
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

var removeHighlights = function(color) {
    boardEl.find('.' + squareClass).removeClass('highlight-' + color);
};

var highlight = function(position, color) {
    boardEl.find('.square-' + position).addClass('highlight-' + color);
};

var updateStatus = function() {
    var status = '';

    var moveColor = 'White';
    if (chess_turn === 'black') {
        moveColor = 'Black';
    }

    status = moveColor + ' to move';
    statusEl.html(status);
};

var prepareHighlights = function() {
    if (!chess_highlights) {
        return;
    }

    var highlights = chess_highlights.split(" ", 6);

    if (highlights.length == 3 || highlights.length == 6) {
        removeHighlights(highlights[2]);
        highlight(highlights[0], highlights[2]);
        highlight(highlights[1], highlights[2]);
    }

    if (highlights.length == 6) {
        removeHighlights(highlights[5]);
        highlight(highlights[3], highlights[5]);
        highlight(highlights[4], highlights[5]);
    }
}

var pieceTheme = function(piece) {
    // wikipedia theme for white pieces
    if (piece.search(/w/) !== -1) {
        return 'js-vendor/tienvx/chessboardjs/img/chesspieces/wikipedia/' + piece + '.png';
    }
  
    // alpha theme for black pieces
    return 'js-vendor/tienvx/chessboardjs/img/chesspieces/wikipedia/' + piece + '.png';
};

var cfg = {
    draggable: true,
    position: chess_start_position,
    orientation: chess_orientation,
    onDrop: onDrop,
    pieceTheme: pieceTheme
};
board = new ChessBoard('board', cfg);

var conn = new ab.Session('ws://localhost:8080',
    function() {
        conn.subscribe('move', function(topic, data) {
            board.move(data.source + '-' + data.target);

            chess_turn = data.color == 'white' ? 'black' : 'white';

            // Highlight the move.
            removeHighlights(data.color);
            highlight(data.source, data.color);
            highlight(data.target, data.color);

            updateStatus();
        });
    },
    function() {
        console.warn('WebSocket connection closed');
    },
    {'skipSubprotocolCheck': true}
);

updateStatus();
prepareHighlights();
