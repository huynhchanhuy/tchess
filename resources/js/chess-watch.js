var game = new Chess(chess_start_position);

var cfg = {
    draggable: false,
    position: chess_start_position,
    pieceTheme: pieceTheme
};
board = new ChessBoard('board', cfg);

updateStatus();
prepareHighlights();
