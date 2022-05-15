class WP_DOUBAN {
    constructor() {
        this.type = "movie";
        this.finished = false;
        this.paged = 1;
        this.genre = "";
        this._create();
    }

    on(t, e, n) {
        var a = document.querySelectorAll(e);
        a.forEach((item) => {
            item.addEventListener(t, n);
        });
    }

    _fetchGenres() {
        fetch(wpd_base.api + "v1/movie/genres")
            .then((response) => response.json())
            .then((t) => {
                // @ts-ignore
                if (t.length) {
                    document.querySelector(".db--genres").innerHTML = t
                        .map((item) => {
                            return `<span class="db--genreItem">${item.name}</span>`;
                        })
                        .join("");
                    this._handleGenreClick();
                }
            });
    }

    _handleGenreClick() {
        this.on("click", ".db--genreItem", (t) => {
            if (t.target.classList.contains("is-active")) return;
            document.querySelector(".db--list").innerHTML = "";
            document.querySelector(".lds-ripple").classList.remove("u-hide");
            if (document.querySelector(".db--genreItem.is-active"))
                document
                    .querySelector(".db--genreItem.is-active")
                    .classList.remove("is-active");
            const self = t.target;
            self.classList.add("is-active");
            this.genre = self.innerText;
            this.paged = 1;
            this.finished = false;
            this._fetchData();
        });
    }

    _fetchData() {
        fetch(
            wpd_base.api +
                "v1/movies?type=" +
                this.type +
                "&paged=" +
                this.paged +
                "&genre=" +
                this.genre
        )
            .then((response) => response.json())
            .then((t) => {
                // @ts-ignore
                if (t.length) {
                    // @ts-ignore
                    document.querySelector(".db--list").innerHTML += t
                        .map((item) => {
                            return `<div class="db--item">${
                                item.is_top250
                                    ? '<span class="top250">Top 250</span>'
                                    : ""
                            }<img src="${
                                item.poster
                            }" referrerpolicy="no-referrer" class="db--image"><div class="ipc-signpost JiEun">${
                                item.create_time
                            }</div><div class="db--score JiEun">${
                                item.douban_score > 0
                                    ? '<svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor" ><path d="M12 20.1l5.82 3.682c1.066.675 2.37-.322 2.09-1.584l-1.543-6.926 5.146-4.667c.94-.85.435-2.465-.799-2.567l-6.773-.602L13.29.89a1.38 1.38 0 0 0-2.581 0l-2.65 6.53-6.774.602C.052 8.126-.453 9.74.486 10.59l5.147 4.666-1.542 6.926c-.28 1.262 1.023 2.26 2.09 1.585L12 20.099z"></path></svg>' +
                                      item.douban_score
                                    : ""
                            }${
                                item.year > 0 ? " · " + item.year : ""
                            }</div><div class="db--title"><a href="${
                                item.link
                            }" target="_blank">${item.name}</a></div>
                </div>
                </div>`;
                        })
                        .join("");
                    document
                        .querySelector(".lds-ripple")
                        .classList.add("u-hide");
                } else {
                    this.finished = true;
                    document
                        .querySelector(".lds-ripple")
                        .classList.add("u-hide");
                }
            });
    }

    _handleScroll() {
        window.addEventListener("scroll", () => {
            var t = window.scrollY || window.pageYOffset;
            // @ts-ignore
            if (
                document.querySelector(".block-more").offsetTop +
                    // @ts-ignore
                    -window.innerHeight <
                    t &&
                document
                    .querySelector(".lds-ripple")
                    .classList.contains("u-hide") &&
                !this.finished
            ) {
                document
                    .querySelector(".lds-ripple")
                    .classList.remove("u-hide");
                this.paged++;
                this._fetchData();
            }
        });
    }

    _handleNavClick() {
        this.on("click", ".db--navItem", (t) => {
            if (t.target.classList.contains("current")) return;
            this.genre = "";
            this.type = t.target.dataset.type;
            if (this.type == "movie") {
                document
                    .querySelector(".db--genres")
                    .classList.remove("u-hide");
            } else {
                document.querySelector(".db--genres").classList.add("u-hide");
            }
            document.querySelector(".db--list").innerHTML = "";
            document.querySelector(".lds-ripple").classList.remove("u-hide");
            document
                .querySelector(".db--navItem.current")
                .classList.remove("current");
            const self = t.target;
            self.classList.add("current");
            this.paged = 1;
            this.finished = false;
            this._fetchData();
        });
    }

    _create() {
        if (document.querySelector(".db--container")) {
            if (document.querySelector(".db--navItem.current")) {
                this.type = document.querySelector(
                    ".db--navItem.current"
                ).dataset.type;
            }
            if (document.querySelector(".db--list").dataset.type)
                this.type = document.querySelector(".db--list").dataset.type;
            if (this.type == "movie") {
                document
                    .querySelector(".db--genres")
                    .classList.remove("u-hide");
            }
            this._fetchGenres();
            this._fetchData();
            this._handleScroll();
            this._handleNavClick();
        }

        if (document.querySelector(".db--collection")) {
            document.querySelectorAll(".db--collection").forEach((item) => {
                this._fetchCollection(item);
            });
        }
    }

    _fetchCollection(item) {
        fetch(
            wpd_base.api +
                "v1/movies?type=" +
                item.dataset.type +
                "&paged=1&genre=&start_time=" +
                item.dataset.start +
                "&end_time=" +
                item.dataset.end
        )
            .then((response) => response.json())
            .then((t) => {
                // @ts-ignore
                if (t.length) {
                    item.innerHTML += t
                        .map((movie) => {
                            return `<div class="doulist-item">
                            <div class="doulist-subject">
                            <div class="db--viewTime JiEun">Marked ${
                                movie.create_time
                            }</div>
                            <div class="doulist-post"><img referrerpolicy="no-referrer" src="${
                                movie.poster
                            }"></div><div class="doulist-content"><div class="doulist-title"><a href="${
                                movie.link
                            }" class="cute" target="_blank" rel="external nofollow">${
                                movie.name
                            }</a></div><div class="rating"><span class="allstardark"><span class="allstarlight" style="width:75%"></span></span><span class="rating_nums">${
                                movie.douban_score
                            }</span></div><div class="abstract">${
                                movie.remark || movie.card_subtitle
                            }</div></div></div></div>`;
                        })
                        .join("");
                }
            });
    }
}

new WP_DOUBAN();
