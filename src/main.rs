mod data;
use crossterm::{
    cursor::{self, MoveTo},
    style::Print,
    terminal::{self, Clear, ClearType},
    QueueableCommand,
};
use std::io::{stdout, Write};
use std::thread::sleep;
use std::time::{Duration, Instant};

const FPS: u64 = 24;

fn main() {
    let (left, top) = get_padding();
    let mut stdout = stdout();

    stdout
        .queue(Clear(ClearType::All))
        .unwrap()
        .queue(cursor::Hide)
        .unwrap();
    let start = Instant::now();
    let mut current_symbol = ' ';
    let mut current_section = 0;
    let mut current_counter = data::STREAM[current_section];
    for i in 0..data::FRAMES {
        for row in 0..data::ROWS {
            stdout.queue(MoveTo(left, top + row as u16)).unwrap();
            for _col in 0..data::COLS {
                stdout.queue(Print(current_symbol)).unwrap();
                current_counter -= 1;
                while current_counter == 0 {
                    current_section += 1;
                    current_counter = data::STREAM[current_section];
                    current_symbol = if current_symbol == 'M' { ' ' } else { 'M' };
                }
            }
        }
        stdout.flush().unwrap();
        while (start.elapsed().as_millis() as f64) < 1000_f64 / (FPS as f64) * (i as f64) {
            sleep(Duration::from_millis(10));
        }
    }
}

fn get_padding() -> (u16, u16) {
    let (t_width, t_height) = terminal::size().unwrap();
    let left = if data::COLS * 8 < t_width as usize {
        (t_width - data::COLS as u16 * 8) / 2
    } else {
        0
    };
    let top = if data::ROWS < t_height as usize {
        (t_height - data::ROWS as u16) / 2
    } else {
        0
    };
    (left, top)
}
