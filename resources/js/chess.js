var board,
game = new Chess(),
boardEl = $('#board'),
statusEl = $('#status'),
squareClass = 'square-55d63'
;

// do not pick up pieces if the game is over
// only pick up pieces for the side to move
var onDragStart = function(source, piece, position, orientation) {
    if (game.game_over() === true ||
        (game.turn() === 'w' && piece.search(/^b/) !== -1) ||
        (game.turn() === 'b' && piece.search(/^w/) !== -1)) {
        return false;
    }
};

var onDrop = function(source, target) {
    // see if the move is legal
    var move = game.move({
        from: source,
        to: target,
        promotion: 'q' // NOTE: always promote to a queen for example simplicity
    });

    // illegal move
    if (move === null) return 'snapback';

    movePiece(move);

    updateStatus();
};

var removeHighlights = function(color) {
  boardEl.find('.' + squareClass).removeClass('highlight-' + color);
};

var highlight = function(position, color) {
  boardEl.find('.square-' + position).addClass('highlight-' + color);
};

var movePiece = function(move) {
    $.post("/move-piece",
    {
        move: move.from + ' ' + move.to + ' ' + 'Q'
    },
    function(data) {
        console.log(data);
    }
    );

    var color = 'white';
    if (move.color === 'b') {
        color = 'black';
    }
    removeHighlights(color);
    highlight(move.from, color);
    highlight(move.to, color);
}

// update the board position after the piece snap 
// for castling, en passant, pawn promotion
var onSnapEnd = function() {
    board.position(game.fen());
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
    position: 'start',
    onDragStart: onDragStart,
    onDrop: onDrop,
    onSnapEnd: onSnapEnd,
    pieceTheme: pieceTheme
};
board = new ChessBoard('board', cfg);

updateStatus();
