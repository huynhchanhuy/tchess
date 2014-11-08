var game;
if (chess_start_position == 'start') {
    game = new Chess();
}
else {
    game = new Chess(chess_start_position);
}

var removeGreySquares = function() {
    $('#board .square-55d63').css('background', '');
};

var greySquare = function(square) {
    var squareEl = $('#board .square-' + square);

    var background = '#a9a9a9';
    if (squareEl.hasClass('black-3c85d') === true) {
        background = '#696969';
    }

    squareEl.css('background', background);
};

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
    removeGreySquares();

    // see if the move is legal
    var move = game.move({
        from: source,
        to: target,
        // TODO: show a dialog for choosing promotion.
        promotion: 'q'
    });

    // illegal move
    if (move === null) return 'snapback';

    $.ajax({
        type: 'POST',
        url: "/move-piece",
        data: {
            move: source + ' ' + target + ' ' + 'Q'
        },
        success: function(data) {
            if (data.code == '200') {
                // Highlight the move.
                var color = game.turn() == 'b' ? 'white' : 'black';
                removeHighlights(color);
                highlight(source, color);
                highlight(target, color);
            }
            else {
                // Move piece back.
                game.undo();
                board.position(game.fen());
            }

            updateStatus();
        },
        dataType: 'json',
        async:true
    });
};

var onMouseoverSquare = function(square, piece) {
    // get list of possible moves for this square
    var moves = game.moves({
        square: square,
        verbose: true
    });

    // exit if there are no moves available for this square
    if (moves.length === 0) return;

    // highlight the square they moused over
    greySquare(square);

    // highlight the possible squares for this piece
    for (var i = 0; i < moves.length; i++) {
        greySquare(moves[i].to);
    }
};

var onMouseoutSquare = function(square, piece) {
    removeGreySquares();
};

// update the board position after the piece snap
// for castling, en passant, pawn promotion
var onSnapEnd = function() {
    board.position(game.fen());
};

var cfg = {
    draggable: true,
    position: chess_start_position,
    onDragStart: onDragStart,
    orientation: chess_orientation,
    onDrop: onDrop,
    onMouseoutSquare: onMouseoutSquare,
    onMouseoverSquare: onMouseoverSquare,
    onSnapEnd: onSnapEnd,
    pieceTheme: pieceTheme
};
board = new ChessBoard('board', cfg);

updateStatus();
prepareHighlights();
