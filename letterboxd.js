const letterboxd = function(username) {
    const rssUri = 'https://letterboxd.com/'+ username +'/rss/';



    const findLatestFilm = function() {
        return 'hej';
    };

    return {
        findLatestFilm : findLatestFilm,
        getUsername : () => {
            return username
        }
    };
};
