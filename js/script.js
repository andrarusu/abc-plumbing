document.addEventListener("DOMContentLoaded", function () {


    /* =====================================================
       CURRENT YEAR
    ===================================================== */

    const currentYear =
        document.getElementById("currentYear");


    if (currentYear) {

        currentYear.textContent =
            new Date().getFullYear();

    }



    /* =====================================================
       MOBILE NAVBAR

       When the burger menu is open and the user
       clicks a navigation link, close the menu.
    ===================================================== */

    const navbarCollapse =
        document.getElementById("mainNavbar");


    const navLinks =
        document.querySelectorAll(
            "#mainNavbar .nav-link"
        );


    navLinks.forEach(function (link) {

        link.addEventListener(
            "click",
            function () {

                if (
                    navbarCollapse &&
                    navbarCollapse.classList.contains("show")
                ) {

                    const collapse =
                        bootstrap.Collapse.getOrCreateInstance(
                            navbarCollapse
                        );


                    collapse.hide();

                }

            }
        );

    });



    /* =====================================================
       QUOTE FORM ELEMENTS
    ===================================================== */

    const quoteModal =
        document.getElementById("quoteModal");


    const quoteForm =
        document.getElementById("quoteForm");


    const serviceSelect =
        document.getElementById("serviceSelect");


    const phoneInput =
        document.getElementById("customerPhone");


    const postcodeInput =
        document.getElementById("customerPostcode");


    const formSuccess =
        document.getElementById("formSuccess");


    const isGitHubPages =
        window.location.hostname === "andrarusu.github.io" ||
        window.location.hostname.endsWith(".github.io");


    const staffLinks =
        document.querySelectorAll(".staff-link");


    if (isGitHubPages) {

        staffLinks.forEach(function (link) {

            link.href =
                "https://github.com/andrarusu/abc-plumbing/blob/main/staff_login.php";

            link.target = "_blank";
            link.rel = "noopener noreferrer";
            link.textContent = "View Staff Login Code";

        });

    }



    /* =====================================================
       AUTOMATIC SERVICE SELECTION

       If user clicks "Get a Quote" under a particular
       service, that service is selected automatically.
    ===================================================== */

    if (quoteModal) {

        quoteModal.addEventListener(
            "show.bs.modal",
            function (event) {

                const button =
                    event.relatedTarget;


                if (!button) {
                    return;
                }


                const selectedService =
                    button.getAttribute(
                        "data-service"
                    );


                if (
                    selectedService &&
                    serviceSelect
                ) {

                    serviceSelect.value =
                        selectedService;

                }

            }
        );

    }



    /* =====================================================
       PHONE VALIDATION
    ===================================================== */


    /*
        Valid examples:

        07700 900123
        01632 960123
        +44 7700 900123
        +44 1632 960123

        Letters are physically removed as the user types.
    */


    function cleanPhoneCharacters(value) {

        return value.replace(
            /[^0-9+\s()\-]/g,
            ""
        );

    }



    function isValidUKPhone(value) {

        const cleaned =
            value.replace(
                /[\s()\-]/g,
                ""
            );


        /*
            UK number:

            0 + 10 digits

            OR

            +44 + 10 digits
        */

        const ukPhonePattern =
            /^(?:0\d{10}|\+44\d{10})$/;


        return ukPhonePattern.test(
            cleaned
        );

    }



    function validatePhone() {

        if (!phoneInput) {
            return true;
        }


        const value =
            phoneInput.value.trim();


        if (!value) {

            phoneInput.setCustomValidity(
                "Please enter your phone number."
            );

            return false;

        }


        if (!isValidUKPhone(value)) {

            phoneInput.setCustomValidity(
                "Please enter a valid UK phone number."
            );

            return false;

        }


        phoneInput.setCustomValidity("");

        return true;

    }



    if (phoneInput) {

        phoneInput.addEventListener(
            "input",
            function () {

                /*
                    Remove letters immediately.
                */

                phoneInput.value =
                    cleanPhoneCharacters(
                        phoneInput.value
                    );


                validatePhone();

            }
        );


        phoneInput.addEventListener(
            "blur",
            validatePhone
        );

    }



    /* =====================================================
       UK POSTCODE VALIDATION
    ===================================================== */


    function isValidUKPostcode(value) {

        /*
            Covers standard UK postcode formats,
            including GIR 0AA.
        */

        const postcodePattern =
            /^(GIR ?0AA|[A-Z]{1,2}[0-9][0-9A-Z]? ?[0-9][A-Z]{2})$/i;


        return postcodePattern.test(
            value.trim()
        );

    }



    function formatUKPostcode(value) {

        /*
            Remove spaces, convert to uppercase,
            then place a space before last 3 characters.

            Example:

            tn91aa
            becomes
            TN9 1AA
        */

        let postcode =
            value
                .replace(/\s+/g, "")
                .toUpperCase();


        if (postcode.length > 3) {

            postcode =
                postcode.slice(0, -3)
                +
                " "
                +
                postcode.slice(-3);

        }


        return postcode;

    }



    function validatePostcode() {

        if (!postcodeInput) {
            return true;
        }


        const value =
            postcodeInput.value.trim();


        if (!value) {

            postcodeInput.setCustomValidity(
                "Please enter your postcode."
            );

            return false;

        }


        if (!isValidUKPostcode(value)) {

            postcodeInput.setCustomValidity(
                "Please enter a valid UK postcode."
            );

            return false;

        }


        postcodeInput.setCustomValidity("");

        return true;

    }



    if (postcodeInput) {

        postcodeInput.addEventListener(
            "input",
            function () {

                /*
                    UK postcodes do not contain
                    punctuation or special characters.
                */

                postcodeInput.value =
                    postcodeInput.value
                        .replace(
                            /[^a-zA-Z0-9\s]/g,
                            ""
                        )
                        .toUpperCase();


                validatePostcode();

            }
        );


        postcodeInput.addEventListener(
            "blur",
            function () {

                postcodeInput.value =
                    formatUKPostcode(
                        postcodeInput.value
                    );


                validatePostcode();

            }
        );

    }



    /* =====================================================
       BOOTSTRAP FORM VALIDATION
    ===================================================== */

    if (quoteForm) {

        quoteForm.addEventListener(
            "submit",
            function (event) {

                event.preventDefault();


                validatePhone();

                validatePostcode();


                if (!quoteForm.checkValidity()) {

                    event.stopPropagation();


                    quoteForm.classList.add(
                        "was-validated"
                    );


                    /*
                        Focus the first invalid field.
                    */

                    const firstInvalid =
                        quoteForm.querySelector(
                            ":invalid"
                        );


                    if (firstInvalid) {

                        firstInvalid.focus();

                    }


                    return;

                }


                quoteForm.classList.add(
                    "was-validated"
                );


                /*
                    PUBLIC GITHUB PAGES DEMO

                    GitHub Pages cannot run PHP/MySQL.
                    Keep the form interactive and validated,
                    but do not submit or store any data.
                */

                if (isGitHubPages) {

                    if (formSuccess) {

                        const successTitle =
                            formSuccess.querySelector("strong");

                        const successText =
                            formSuccess.querySelector("p");


                        if (successTitle) {

                            successTitle.textContent =
                                "Demo request completed.";

                        }


                        if (successText) {

                            successText.textContent =
                                "No information was submitted or stored. This public preview demonstrates the form and client-side validation.";

                        }


                        formSuccess.classList.remove(
                            "d-none"
                        );


                        formSuccess.scrollIntoView({
                            behavior: "smooth",
                            block: "nearest"
                        });

                    }


                    return;

                }


                /*
                    LOCAL / PHP HOSTING MODE

                    Preserve the real project behaviour:
                    submit_quote.php saves the request to MySQL.
                */

                fetch("submit_quote.php", {
                    method: "POST",
                    body: new FormData(quoteForm)
                })
                .then(async function (response) {

                    const result =
                        await response.json();


                    if (
                        !response.ok ||
                        !result.success
                    ) {

                        throw new Error(
                            result.message ||
                            "Could not save the request."
                        );

                    }


                    return result;

                })
                .then(function (result) {

                    if (formSuccess) {

                        const successTitle =
                            formSuccess.querySelector("strong");

                        const successText =
                            formSuccess.querySelector("p");


                        if (successTitle) {

                            successTitle.textContent =
                                "Quote request saved.";

                        }


                        if (successText) {

                            successText.textContent =
                                result.message;

                        }


                        formSuccess.classList.remove(
                            "d-none"
                        );

                    }


                    // Close the modal after the successful save.
                    window.setTimeout(
                        function () {

                            const modalElement =
                                document.getElementById(
                                    "quoteModal"
                                );


                            const modal =
                                modalElement
                                    ? bootstrap.Modal.getInstance(
                                        modalElement
                                    )
                                    : null;


                            if (modal) {

                                modal.hide();

                            }

                        },
                        2500
                    );

                })
                .catch(function (error) {

                    alert(error.message);

                });


            }
        );

    }



    /* =====================================================
       RESET FORM WHEN MODAL CLOSES
    ===================================================== */

    if (quoteModal) {

        quoteModal.addEventListener(
            "hidden.bs.modal",
            function () {

                if (!quoteForm) {
                    return;
                }


                quoteForm.reset();


                quoteForm.classList.remove(
                    "was-validated"
                );


                if (phoneInput) {

                    phoneInput.setCustomValidity("");

                }


                if (postcodeInput) {

                    postcodeInput.setCustomValidity("");

                }


                if (formSuccess) {

                    formSuccess.classList.add(
                        "d-none"
                    );

                }

            }
        );

    }



    /* =====================================================
       INTERACTIVE SERVICE AREA MAP
    ===================================================== */

    const mapElement =
        document.getElementById(
            "serviceMap"
        );


    if (
        mapElement &&
        typeof L !== "undefined"
    ) {


        /*
            Approximate town-centre coordinates.
        */

        const serviceLocations = {

            tonbridge: {

                name:
                    "Tonbridge",

                latitude:
                    51.196967,

                longitude:
                    0.275186

            },


            "tunbridge-wells": {

                name:
                    "Tunbridge Wells",

                latitude:
                    51.134556,

                longitude:
                    0.263488

            },


            sevenoaks: {

                name:
                    "Sevenoaks",

                latitude:
                    51.273469,

                longitude:
                    0.195958

            },


            maidstone: {

                name:
                    "Maidstone",

                latitude:
                    51.2748,

                longitude:
                    0.5232

            },


            "west-malling": {

                name:
                    "West Malling",

                latitude:
                    51.294704,

                longitude:
                    0.408825

            }

        };



        /* -----------------------------------------
           CREATE MAP
        ------------------------------------------ */

        const map =
            L.map(
                "serviceMap",
                {

                    /*
                        Stops the page from unexpectedly zooming
                        while the user is scrolling.

                        Zoom buttons and pinch zoom still work.
                    */

                    scrollWheelZoom:
                        false

                }
            );



        /* -----------------------------------------
           OPENSTREETMAP TILES
        ------------------------------------------ */

        L.tileLayer(
            "https://tile.openstreetmap.org/{z}/{x}/{y}.png",
            {

                maxZoom:
                    19,

                attribution:
                    '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'

            }
        ).addTo(map);



        /* -----------------------------------------
           CUSTOM MARKER
        ------------------------------------------ */

        const customIcon =
            L.divIcon(
                {

                    className:
                        "custom-map-marker",

                    html:
                        `
                            <div class="map-marker-dot">
                                <i class="bi bi-wrench-adjustable"></i>
                            </div>
                        `,

                    iconSize:
                        [34, 34],

                    iconAnchor:
                        [17, 34],

                    popupAnchor:
                        [0, -32]

                }
            );



        const markers =
            {};


        const bounds =
            [];



        /* -----------------------------------------
           ADD ALL LOCATIONS
        ------------------------------------------ */

        Object.entries(
            serviceLocations
        ).forEach(

            function ([key, location]) {


                const coordinates = [

                    location.latitude,

                    location.longitude

                ];


                bounds.push(
                    coordinates
                );


                const marker =
                    L.marker(
                        coordinates,
                        {
                            icon:
                                customIcon
                        }
                    )
                    .addTo(map)
                    .bindPopup(
                        `
                            <strong>${location.name}</strong>

                            <br>

                            ABC Plumbing service area
                        `
                    );


                markers[key] =
                    marker;

            }

        );



        /* -----------------------------------------
           SHOW ALL LOCATIONS ON LOAD
        ------------------------------------------ */

        const allBounds =
            L.latLngBounds(
                bounds
            );


        map.fitBounds(
            allBounds,
            {

                padding:
                    [35, 35]

            }
        );



        /* Map scale */

        L.control.scale(
            {

                imperial:
                    true,

                metric:
                    true

            }
        ).addTo(map);



        /* -----------------------------------------
           AREA BUTTONS
        ------------------------------------------ */

        const locationButtons =
            document.querySelectorAll(
                "[data-map-location]"
            );


        function removeActiveAreaButtons() {

            locationButtons.forEach(
                function (button) {

                    button.classList.remove(
                        "active"
                    );

                }
            );

        }



        locationButtons.forEach(
            function (button) {


                button.addEventListener(
                    "click",
                    function () {


                        const key =
                            button.dataset.mapLocation;


                        const location =
                            serviceLocations[key];


                        const marker =
                            markers[key];


                        if (
                            !location ||
                            !marker
                        ) {

                            return;

                        }


                        removeActiveAreaButtons();


                        button.classList.add(
                            "active"
                        );


                        map.flyTo(

                            [
                                location.latitude,
                                location.longitude
                            ],

                            13,

                            {
                                duration:
                                    1.1
                            }

                        );


                        /*
                            Wait until movement has started,
                            then show popup.
                        */

                        window.setTimeout(
                            function () {

                                marker.openPopup();

                            },
                            700
                        );

                    }
                );

            }
        );



        /* -----------------------------------------
           VIEW ALL AREAS
        ------------------------------------------ */

        const showAllAreas =
            document.getElementById(
                "showAllAreas"
            );


        if (showAllAreas) {

            showAllAreas.addEventListener(
                "click",
                function () {


                    removeActiveAreaButtons();


                    map.flyToBounds(
                        allBounds,
                        {

                            padding:
                                [35, 35],

                            duration:
                                1.1

                        }
                    );

                }
            );

        }



        /*
            Makes sure Leaflet calculates its
            size correctly after page rendering.
        */

        window.setTimeout(
            function () {

                map.invalidateSize();

            },
            200
        );

    }

});