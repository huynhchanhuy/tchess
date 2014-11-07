var boardEl = $('#board'),
statusEl = $('#status'),
squareClass = 'square-55d63'
;

var removeHighlights = function(color) {
    boardEl.find('.' + squareClass).removeClass('highlight-' + color);
};

var highlight = function(position, color) {
    boardEl.find('.square-' + position).addClass('highlight-' + color);
};

var updateStatus = function() {
    var status = '';

    var moveColor = 'White';
    if (game.turn() === 'b') {
      moveColor = 'Black';
    }

    // checkmate?
    if (game.in_checkmate() === true) {
      status = 'Game over, ' + moveColor + ' is in checkmate.';
    }

    // draw?
    else if (game.in_draw() === true) {
      status = 'Game over, drawn position';
    }

    // game still on
    else {
      status = moveColor + ' to move';

      // check?
      if (game.in_check() === true) {
        status += ', ' + moveColor + ' is in check';
      }
    }

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
};

var conn = new ab.Session('ws://localhost:8080',
    function() {
        conn.subscribe('move', function(topic, data) {
            board.move(data.source + '-' + data.target);

            if (!data.castling) {
                // Highlight the move.
                removeHighlights(data.color);
                highlight(data.source, data.color);
                highlight(data.target, data.color);
            }

            updateStatus();
        });
    },
    function() {
        console.warn('WebSocket connection closed');
    },
    {'skipSubprotocolCheck': true}
);
