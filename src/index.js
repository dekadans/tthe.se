import Parser from 'rss-parser';

let parser = new Parser({
    customFields : {
        item : ['letterboxd:watchedDate']
    }
});

(async () => {
    let feed = await parser.parseURL('filmfeed.php');
    let latestFilm = feed.items[0];
    console.log(latestFilm);
})();
