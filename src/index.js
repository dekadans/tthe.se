import letterboxd from './letterboxd';

(async () => {
    let l = letterboxd('filmfeed.php');
    let latestFilm = await l.latest();
    console.log(latestFilm);
})();
