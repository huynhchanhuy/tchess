var chess;
if (chess_start_position == 'start') {
    chess = new Chess();
}
else {
    chess = new Chess(chess_start_position);
}

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
    // TODO: show a dialog for choosing promotion.
    promotion: 'q'
  });

  // illegal move
  if (move === null) return 'snapback';

    // Highlight the move.
    var color = chess.turn() == 'b' ? 'white' : 'black';
    removeHighlights(color);
    highlight(source, color);
    highlight(target, color);

    updateStatus();

    $.ajax({
        type: 'POST',
        url: "/move-piece",
        data: {
            move: source + ' ' + target + ' ' + 'Q'
        },
        success: function(data) {
            if (data.code == '200') {
                // Do some things here.
            }
        },
        dataType: 'json',
        async:false
    });
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
    onSnapEnd: onSnapEnd,
    pieceTheme: pieceTheme
};
board = new ChessBoard('board', cfg);

updateStatus();
prepareHighlights();
