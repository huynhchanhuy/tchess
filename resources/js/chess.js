var board,
statusEl = $('#status');

var pieceTheme = function(piece) {
    // wikipedia theme for white pieces
    if (piece.search(/w/) !== -1) {
        return '/js-vendor/tienvx/chessboardjs/img/chesspieces/wikipedia/' + piece + '.png';
    }
  
    // alpha theme for black pieces
    return '/js-vendor/tienvx/chessboardjs/img/chesspieces/wikipedia/' + piece + '.png';
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
