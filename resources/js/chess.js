var board;

var pieceTheme = function(piece) {
    // wikipedia theme for white pieces
    if (piece.search(/w/) !== -1) {
        return '/js-vendor/tienvx/chessboardjs/img/chesspieces/wikipedia/' + piece + '.png';
    }
  
    // alpha theme for black pieces
    return '/js-vendor/tienvx/chessboardjs/img/chesspieces/wikipedia/' + piece + '.png';
};
