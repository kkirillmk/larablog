<?php
/**
 * Функция склонения числительных в русском языке
 *
 * @param int    $number Число которое нужно просклонять
 * @param array  $titles Массив слов для склонения
 * @return string
 **/
//Пример использования  pluralForm($filmStats['count'], array('фильм', 'фильма', 'фильмов'))
function pluralForm($number, $titles) {
    $cases = array (2, 0, 1, 1, 1, 2);
    echo $number.' '.$titles[ ($number%100>4 && $number%100<20)? 2: $cases[min($number%10, 5)] ];
}