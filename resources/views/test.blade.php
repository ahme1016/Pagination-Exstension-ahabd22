<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movie App</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://kit.fontawesome.com/d826f0fb4b.js" crossorigin="anonymous"></script>
    <style>
        /* Add your CSS styles here */
        * {
            box-sizing: border-box;
        }

        body {
            font-family: "Poppins", sans-serif;
            margin: 0;
            background-color: #000;
            color: white;
            min-height: 100vh;
            height: 100%;
        }

        main {
            display: flex;
            flex-wrap: wrap;
        }

        .movie {
            background-color: #222;
            border-radius: 3px;
            box-shadow: 0 4px 5px rgba(0, 0, 0, 0.2);
            width: 300px;
            margin: 1rem;
            cursor: pointer; /* Add this to indicate clickable elements */
        }

        .movie img {
            width: 100%;
        }

        .movie-info {
            color: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.5rem 1rem 1rem;
            letter-spacing: 0.5px;
        }

        .movie-info h3 {
            margin: 0;
        }

        .movie-info span {
            background-color: #222;
            border-radius: 3px;
            padding: 0.25rem 0.5rem;
        }

    </style>
</head>

<body>
@include('search')
<div id="innerbody">
    <div id="title-div">
        <h1>Popular movies</h1>
        @if (session()->has('user'))
            {{ session('user')->name }}
        @endif
    </div>
    <div id="searchPoster"></div>
    <main>
        <!-- Movies will be appended here -->
    </main>
    <h1 id="watchlist-title">Your Watchlist</h1>
    <div id="watchlist-div">
        <!-- Watchlist content will be added here -->
    </div>
</div>
<script>
    const APIURL = "https://api.themoviedb.org/3/discover/movie?include_adult=false&include_video=false&language=en-US&page=1&sort_by=popularity.desc&api_key=7356f6c781f842026367b8baa225abdb&page=1";
    const IMGPATH = "https://image.tmdb.org/t/p/w500";

    const main = document.querySelector('main');
    const watchlistDiv = document.querySelector('#watchlist-div');

    async function getMovies() {
        const resp = await fetch(APIURL);
        const respData = await resp.json();

        console.log(respData);

        respData.results.forEach(movie => {
            const {
                id,
                poster_path,
                title,
                vote_average
            } = movie;

            const movieEl = document.createElement('div');
            movieEl.classList.add('movie');

            movieEl.innerHTML = `
                    <img
                        src="${IMGPATH + poster_path}"
                        alt="${title}"
                    />
                    <div class="movie-info">
                        <h3>${title}</h3>
                        <span>${vote_average}</span>
                    </div>
                `;

            movieEl.addEventListener('click', () => {
                window.location.href = `/movie/${id}`;
            });

            main.appendChild(movieEl);
        });

        return respData;
    }

    getMovies(); // initialization of movies

    async function getPosterPath(movie_id) {
        return fetch(`/api/getPosterPath/${movie_id}`, {
            method: "GET"
        }).then(async (result) => {
            return result.json();
        });
    }

    async function getWatchlist(id) {
        return fetch(`/api/getUserWatchlist/${id}`, {
            method: "GET"
        }).then(async (result) => {
            return result.json();
        });
    }

    document.addEventListener('DOMContentLoaded', async () => {
        const response = await getWatchlist(
                @if (session()->has('user'))
                    {{ session('user')->id }}
                    @else
                -1
            @endif
        );

        const div = document.querySelector('#watchlist-div');
        if (response.length === 0) {
            const text = document.createElement('p');
            text.innerHTML = @if (session()->has('user'))
                "Your watchlist is empty. Go to a movies page to add it to your watchlist";
            @else
                "Login to access your watchlist";
            @endif

            text.setAttribute('id', 'emptywatchlist');
            div.appendChild(text);
        }

        for (let i = 0; i < 6; i++) {
            const movie = document.createElement('img');
            const a = document.createElement('a');
            a.setAttribute('class', 'redposter');
            if (i < response.length) {
                const posterpath = await getPosterPath(response[i].movie_id);
                movie.setAttribute('src', `https://image.tmdb.org/t/p/w500${posterpath.poster_path}`);
                a.setAttribute('href', `/movie/${response[i].movie_id}`);
            } else {
                movie.style.visibility = 'hidden';
            }

            movie.setAttribute('class', 'poster');
            a.appendChild(movie);
            div.appendChild(a);
        }
    });

    document.querySelector("#form").addEventListener("submit", async (event) => {
        event.preventDefault();
        const input = document.querySelector("#input").value;
        const result = await $.ajax({
            url: 'api/test/' + input,
            type: "GET",
        });

        document.querySelector('#searchPoster').innerHTML +=
            `<img class="poster" src="https://image.tmdb.org/t/p/w500${result}">`;
    });
</script>
</body>

</html>
