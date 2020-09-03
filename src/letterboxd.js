import Parser from 'rss-parser';

let letterboxd = (url) => {
    let parser = new Parser({
        customFields : {
            item : [
                'letterboxd:watchedDate',
                'letterboxd:filmTitle',
                'letterboxd:filmYear',
                'letterboxd:memberRating'
            ]
        }
    });

    async function latest() {
        try {
            let feed = await parser.parseURL(url);
            let latestItem = feed.items[0];
            return makeFilm(latestItem);
        } catch (e) {
            return false;
        }
    }

    function makeFilm(item) {
        return {
            title : item['letterboxd:filmTitle'],
            year : item['letterboxd:filmYear'],
            watched : item['letterboxd:watchedDate'],
            rating : item['letterboxd:memberRating'],
            poster : parsePosterUrl(item.content)
        }
    }

    function parsePosterUrl(html) {
        let template = document.createElement('template');
        template.innerHTML = html.trim();
        let image = template.content.querySelector('img');

        return image ? image.src : null;
    }

    return {
        latest : latest
    };
};

export default letterboxd;
