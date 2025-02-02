<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Movie Website</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://kit.fontawesome.com/d826f0fb4b.js" crossorigin="anonymous"></script>
    <style>
        /* Add your CSS styles here */
        * {
            box-sizing: border-box;
        }

        h1{
            background: rebeccapurple;
            margin: 0;
            padding: 0;
        }

        #title-div {
            text-align: center;
            padding: 1rem;
        }

        #innerbody{
            background: rebeccapurple;
        }

        header.search{
            background: rebeccapurple;
            width: auto;
        }

        div{
            width: auto;
        }

        body {
            font-family: "Poppins", sans-serif;
            margin: 0;
            background-color: #000;
            color: white;
            min-height: 100vh;
            height: 100%;
            zoom: 80%;
        }

        main {
            display: flex;
            flex-wrap: wrap;
            background: rebeccapurple;

        }

        .movie {
            background-color: gray;
            border-radius: 3px;
            box-shadow: 0 4px 5px rgba(0, 0, 0, 0.2);
            width: 300px;
            margin: 1rem;
            position: relative;
            cursor: pointer;
            overflow: hidden;
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
            font-weight: bold;
        }

        .movie-info span.green {
            color: limegreen;
        }

        .movie-info span.orange {
            color: orange;

        }

        .movie-info span.red {
            color: red;
        }

        #next-page, #prev-page {
            display: block;
            margin: 20px auto; /* Adjust the margin as needed for spacing */
            padding: 10px 20px;
            font-size: 16px;
            background-color: rebeccapurple;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            font-weight: bold;
        }

        #next-page:hover, #prev-page:hover {
            background-color: #6b2c80; /* Darken the color on hover */
        }

        .overview {
            background-color: white;
            color: black;
            padding: 2rem;
            position: absolute;
            left: 0;
            right: 0;
            bottom: 0;
            transform: translateY(100%);
            transition: transform 0.3s ease-in;
        }

        .movie:hover .overview {
            transform: translateY(0);
        }
        .overview h4 {
            margin-top: 0;
        }
    </style>
</head>

<body>
@include('search')
<div id="innerbody">
    <div id="title-div">
        <h1>Movies</h1>
        @if (session()->has('user'))
            {{ session('user')->name }}
        @endif
    </div>
    <div id="searchPoster"></div>
    <main>
        <!-- Movies will be appended here -->
    </main>
    <!-- buttons to load the next and previous pages -->
    <button id="prev-page">Load Previous Page</button>
    <button id="next-page">Load Next Page</button>
    <h1 id="watchlist-title">Your Watchlist</h1>
    <div id="watchlist-div">
        <!-- Watchlist content will be added here -->
    </div>
</div>
<script>
    const IMGPATH = "https://image.tmdb.org/t/p/w500";
    const main = document.querySelector('main');
    let currentPage = 1;

    // Method for fetching movies

    async function getMovies() {
        const resp = await fetch(
            "https://api.themoviedb.org/3/discover/movie?include_adult=false&include_video=false&language=en-US&sort_by=popularity.desc&api_key=7356f6c781f842026367b8baa225abdb&page=" +
            currentPage
        );
        const respData = await resp.json();

        console.log(respData);

        respData.results.forEach((movie) => {
            const { id, poster_path, title, vote_average, overview } = movie;

            // Check if the movie already exists in the container
            const existingMovie = main.querySelector(`.movie[data-id="${id}"]`);
            if (!existingMovie) {
                const movieEl = document.createElement('div');
                movieEl.classList.add('movie');
                movieEl.setAttribute('data-id', id);

                movieEl.innerHTML = `
                    <img
                        src="${IMGPATH + poster_path}"
                        alt="${title}"
                    />
                    <div class="movie-info">
                        <h3>${title}</h3>
                        <span class="${getMoviesByRating(vote_average)}">${vote_average}</span>
                    </div>
                    <div class= "overview">
                        <h4>Overview:</h4>
                        ${overview}
                    </div>
                `;

                movieEl.addEventListener('click', () => {
                    window.location.href = `/movie/${id}`;
                });

                main.appendChild(movieEl);
            }
        });

        return respData;
    }

    function getMoviesByRating(vote){
        if (vote >= 8){
            return "green";
        }
        else if (vote >= 6){
            return "orange";
        }
        else{
            return "red";
        }
    }

    // Method to clear existing movies
    function clearMovies() {
        main.innerHTML = '';
    }

    // Method for loading the next set of movies via. pagination.

    async function loadNextPage() {
        const nextPageButton = document.getElementById('next-page');
        nextPageButton.addEventListener('click', async () => {
            // Increment the page counter
            currentPage++;

            // Clear existing movies before loading new ones
            clearMovies();

            // Load the next page of movies
            const nextPageData = await getMovies();

            // Check if there are more pages to load
            if (currentPage >= nextPageData.total_pages) {
                nextPageButton.style.display = 'none'; // Hide the next button if no more pages
            }

            // Show the previous button once we are on the second page
            document.getElementById('prev-page').style.display = 'block';
        });
    }

    // Method for loading the previous set of movies via. pagination.

    async function loadPrevPage() {
        const prevPageButton = document.getElementById('prev-page');
        prevPageButton.addEventListener('click', async () => {
            // Decrease the page counter
            currentPage--;

            // Clear existing movies before loading new ones
            clearMovies();

            // Load the previous page of movies
            const prevPageData = await getMovies();

            // Check if there are more pages to load
            if (currentPage <= 1) {
                prevPageButton.style.display = 'none'; // Hide the previous button if no previous pages
            }

            // Show the next button once we are back to the first page
            document.getElementById('next-page').style.display = 'block';
        });
    }

    document.addEventListener('DOMContentLoaded', async () => {
        // Load both next and previous page buttons
        await loadNextPage();
        await loadPrevPage();

        const initialData = await getMovies(); // Initial set of movies

        // Check if there are more pages to load
        if (currentPage >= initialData.total_pages) {
            document.getElementById('next-page').style.display = 'none'; // Hide the next button if no more pages
        }

        // Hide the previous button
        document.getElementById('prev-page').style.display = 'none';
    });
</script>
</body>
</html>
