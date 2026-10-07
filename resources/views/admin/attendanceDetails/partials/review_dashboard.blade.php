```blade
{{-- ============================================================
     ATTENDANCE REVIEW DESK
     ------------------------------------------------------------
     Ye complete section sirf admin users ko dikhaya jayega.
     auth()->user()?->is_admin == 1
     ka matlab:
     - User logged in hai
     - Aur user admin hai
     ============================================================ --}}

@if(auth()->user()?->is_admin == 1)

    {{-- ========================================================
         1. ATTENDANCE REVIEW BOARD
         --------------------------------------------------------
         Is section mein:
         - In Review count
         - Suspicious count
         - Live attendance review cards
         display hote hain.
         ======================================================== --}}

    <section class="review-board mt-4" id="attendanceReviewBoard">

        {{-- =======================
             HEADER
             ======================= --}}
        <div class="review-heading">

            {{-- Heading / Title --}}
            <div>
                <span class="review-kicker">
                    LIVE LOCATION CONTROL
                </span>

                <h5>Attendance Review Desk</h5>
            </div>

            {{-- ==========================
                 REVIEW COUNTERS
                 ==========================
                 In Review:
                 - Kitne attendance records
                   abhi review mein hain.

                 Suspicious:
                 - Kitne records suspicious
                   mark hue hain.
                 ========================== --}}
            <div class="review-counts">

                {{-- In Review Button --}}
                <button
                    type="button"
                    class="review-pill amber"
                    onclick="window.openReviewList('in_review')"
                >
                    <b id="reviewCount">0</b>
                    In review
                </button>

                {{-- Suspicious Button --}}
                <button
                    type="button"
                    class="review-pill red"
                    onclick="window.openReviewList('suspicious')"
                >
                    <b id="suspiciousCount">0</b>
                    Suspicious
                </button>

            </div>
        </div>


        {{-- ====================================================
             REVIEW CARDS CONTAINER
             ----------------------------------------------------
             JavaScript API se attendance records load karke
             yahan cards generate karega.
             ==================================================== --}}

        <div id="reviewCards" class="review-grid">

            {{-- Initial loading message --}}
            <div class="review-empty">
                Checking live attendance reviews...
            </div>

        </div>

    </section>


    {{-- ========================================================
         2. ATTENDANCE DETAIL MODAL
         --------------------------------------------------------
         Kisi attendance card ke "Details" button par click
         karne par ye modal open hota hai.
         ======================================================== --}}

    <div
        class="modal fade"
        id="reviewDetailModal"
        tabindex="-1"
    >

        <div class="modal-dialog modal-lg modal-dialog-centered">

            <div class="modal-content">

                {{-- Modal Header --}}
                <div class="modal-header">

                    <h5 class="modal-title">
                        Attendance Investigation
                    </h5>

                    <button
                        type="button"
                        class="close"
                        data-dismiss="modal"
                    >
                        &times;
                    </button>

                </div>

                {{-- Modal Body
                     JavaScript yahan employee ki complete
                     attendance details inject karega.
                --}}
                <div
                    class="modal-body"
                    id="reviewDetailBody"
                ></div>

            </div>

        </div>

    </div>


    {{-- ========================================================
         3. REVIEW LIST MODAL
         --------------------------------------------------------
         "In Review" ya "Suspicious" counter par click karne
         par filtered records is modal mein show honge.
         ======================================================== --}}

    <div
        class="modal fade"
        id="reviewListModal"
        tabindex="-1"
    >

        <div
            class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable"
        >

            <div class="modal-content">

                {{-- Modal Header --}}
                <div class="modal-header">

                    <h5
                        class="modal-title"
                        id="reviewListTitle"
                    >
                        Attendance Reviews
                    </h5>

                    <button
                        type="button"
                        class="close"
                        data-dismiss="modal"
                    >
                        &times;
                    </button>

                </div>

                {{-- Modal Body --}}
                <div class="modal-body">

                    {{-- Filtered attendance cards yahan render honge --}}
                    <div
                        id="reviewListBody"
                        class="review-grid"
                    ></div>

                </div>

            </div>

        </div>

    </div>


    {{-- ========================================================
         4. CSS
         --------------------------------------------------------
         Attendance Review Desk ka complete UI design.
         ======================================================== --}}

    <style>

        /* ======================================================
           MAIN REVIEW BOARD
           ====================================================== */

        .review-board {
            background: #fff;
            border: 1px solid #dfe5ec;
            border-top: 4px solid #334155;
            border-radius: 8px;
            padding: 18px;
        }


        /* ======================================================
           HEADER
           ====================================================== */

        .review-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .review-heading h5 {
            margin: 2px 0 0;
            color: #172b4d;
        }


        /* Small uppercase heading */
        .review-kicker {
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 1.2px;
            color: #64748b;
        }


        /* ======================================================
           COUNTER BUTTONS
           ====================================================== */

        .review-counts {
            display: flex;
            gap: 8px;
        }

        .review-pill {
            padding: 6px 10px;
            border: 0;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
        }


        /* In Review = Amber */
        .review-pill.amber {
            background: #fff7ed;
            color: #9a3412;
        }


        /* Suspicious = Red */
        .review-pill.red {
            background: #fef2f2;
            color: #b91c1c;
        }


        /* ======================================================
           REVIEW CARD GRID
           ====================================================== */

        .review-grid {
            display: grid;

            /*
             * Cards automatically responsive honge.
             * Minimum width = 280px
             */
            grid-template-columns:
                repeat(auto-fill, minmax(280px, 1fr));

            gap: 12px;
            margin-top: 14px;
        }


        /* ======================================================
           INDIVIDUAL REVIEW CARD
           ====================================================== */

        .review-card {
            border: 1px solid #e2e8f0;

            /* Normal review ke liye amber left border */
            border-left: 5px solid #f59e0b;

            border-radius: 6px;
            padding: 14px;
            background: #fff;
        }


        /* Suspicious record = Red left border */
        .review-card.suspicious {
            border-left-color: #dc2626;
        }


        /* ======================================================
           CARD TOP AREA
           ====================================================== */

        .review-card-top {
            display: flex;
            justify-content: space-between;
            gap: 10px;
        }

        .review-name {
            font-weight: 800;
            color: #1e293b;
        }

        .review-code,
        .review-info {
            font-size: 12px;
            color: #64748b;
        }


        /* ======================================================
           TIMER
           ====================================================== */

        .review-timer {
            font: 800 18px monospace;
            color: #b45309;
        }


        /* Suspicious card ka timer red */
        .review-card.suspicious .review-timer {
            color: #dc2626;
        }


        /* ======================================================
           DISTANCE INFORMATION
           ====================================================== */

        .review-distance {
            font-size: 13px;
            margin: 12px 0;
        }


        /* ======================================================
           BUTTON AREA
           ====================================================== */

        .review-actions {
            display: flex;
            gap: 7px;
            flex-wrap: wrap;
        }


        /* Empty/loading message */
        .review-empty {
            padding: 28px;
            text-align: center;
            color: #64748b;
            grid-column: 1 / -1;
        }


        /* ======================================================
           MOBILE RESPONSIVE
           ====================================================== */

        @media (max-width: 650px) {

            .review-heading {
                align-items: flex-start;
                flex-direction: column;
            }

            .review-counts {
                width: 100%;
            }

            .review-pill {
                flex: 1;
                text-align: center;
            }
        }

    </style>


    {{-- ========================================================
         5. JAVASCRIPT
         --------------------------------------------------------
         Ye complete functionality handle karta hai:
         - API se records load
         - Cards create
         - Counters update
         - Details modal
         - Review/Approve
         - Reject
         - Suspicious
         - Countdown timer
         - Auto refresh
         ======================================================== --}}

    <script>

        (function () {

            /* ==================================================
               GLOBAL LOCAL DATA

               API se jo attendance records milenge,
               unko is array mein store kiya jayega.
               ================================================== */

            let reviewItems = [];


            /* ==================================================
               HTML ESCAPE FUNCTION

               User/API data ko directly HTML mein insert karte
               waqt XSS risk ko reduce karta hai.

               Example:
               <script> ko harmless text bana deta hai.
               ================================================== */

            const esc = value =>
                String(value ?? '').replace(
                    /[&<>'"]/g,
                    char => ({
                        '&': '&amp;',
                        '<': '&lt;',
                        '>': '&gt;',
                        "'": '&#39;',
                        '"': '&quot;'
                    }[char])
                );


            /* ==================================================
               COUNTDOWN SECONDS

               Deadline aur current time ka difference
               seconds mein calculate karta hai.

               Agar deadline pass ho chuki hai to 0 return karega.
               ================================================== */

            const seconds = date =>
                date
                    ? Math.max(
                        0,
                        Math.floor(
                            (
                                new Date(date).getTime()
                                - Date.now()
                            ) / 1000
                        )
                    )
                    : 0;


            /* ==================================================
               CLOCK FORMAT

               Seconds ko MM:SS format mein convert karta hai.

               Example:
               125 seconds => 02:05
               ================================================== */

            const clock = secondsValue =>
                String(Math.floor(secondsValue / 60)).padStart(2, '0')
                + ':'
                + String(secondsValue % 60).padStart(2, '0');


            /* ==================================================
               DELAY TEXT

               Employee office area mein pahunch chuka hai
               ya nahi, uske according message banata hai.
               ================================================== */

            const delayText = item =>
                item.entered_at
                    ? 'Reached '
                        + clock(
                            Number(
                                item.arrival_delay_seconds || 0
                            )
                        )
                        + ' late'
                    : 'Not reached yet';


            /* ==================================================
               CREATE REVIEW CARD

               API se aaye ek attendance record ko HTML card
               mein convert karta hai.
               ================================================== */

            function card(item) {

                /* ----------------------------------------------
                   SUSPICIOUS RECORD KE ACTIONS
                   ---------------------------------------------- */

                const actions =
                    item.status === 'suspicious'

                        ? `
                            <button
                                class="btn btn-sm btn-success"
                                onclick="window.showAttendanceReview(${item.id})"
                            >
                                <i class="fas fa-check mr-1"></i>
                                Review & Approve
                            </button>

                            <button
                                class="btn btn-sm btn-danger"
                                onclick="window.setAttendanceReview(
                                    ${item.id},
                                    'reject'
                                )"
                            >
                                <i class="fas fa-times mr-1"></i>
                                Reject / Absent
                            </button>
                        `

                        : `
                            <span class="review-info">
                                <i class="fas fa-clock mr-1"></i>
                                Approval unlocks after timer expiry.
                            </span>
                        `;


                /* ----------------------------------------------
                   COMPLETE CARD HTML
                   ---------------------------------------------- */

                return `
                    <article class="review-card ${item.status}">

                        <div class="review-card-top">

                            <div>

                                <div class="review-name">
                                    ${esc(item.employee)}
                                </div>

                                <div class="review-code">
                                    ${esc(item.employee_code || '')}

                                    ${
                                        item.office
                                            ? ' | ' + esc(item.office)
                                            : ''
                                    }
                                </div>

                            </div>


                            {{-- Timer / Delay --}}
                            <div
                                class="review-timer"
                                data-deadline="${esc(item.deadline)}"
                            >
                                ${
                                    item.status === 'suspicious'
                                        ? esc(delayText(item))
                                        : clock(seconds(item.deadline))
                                }
                            </div>

                        </div>


                        {{-- Distance --}}
                        <div class="review-distance">

                            <i class="fas fa-route mr-1"></i>

                            ${
                                item.distance === null
                                    ? 'Outside area'
                                    : Math.round(item.distance)
                                        + ' m from office'
                            }

                            ${
                                item.radius
                                    ? '(allowed '
                                        + item.radius
                                        + ' m)'
                                    : ''
                            }

                        </div>


                        {{-- System Reason --}}
                        <div class="review-info">
                            ${esc(
                                item.reason
                                || 'Outside configured office area'
                            )}
                        </div>


                        {{-- Action Buttons --}}
                        <div class="review-actions mt-3">

                            {{-- Details --}}
                            <button
                                class="btn btn-sm btn-outline-dark"
                                onclick="window.showAttendanceReview(
                                    ${item.id}
                                )"
                            >
                                <i class="fas fa-search mr-1"></i>
                                Details
                            </button>

                            ${actions}

                        </div>

                    </article>
                `;
            }


            /* ==================================================
               RENDER MAIN REVIEW BOARD

               - In Review count update
               - Suspicious count update
               - Cards display
               ================================================== */

            function render() {

                const box =
                    document.getElementById('reviewCards');


                /* In Review Count */
                document.getElementById('reviewCount').textContent =
                    reviewItems.filter(
                        item => item.status === 'in_review'
                    ).length;


                /* Suspicious Count */
                document.getElementById('suspiciousCount').textContent =
                    reviewItems.filter(
                        item => item.status === 'suspicious'
                    ).length;


                /* No records */
                if (!reviewItems.length) {

                    box.innerHTML = `
                        <div class="review-empty">

                            <i
                                class="fas fa-check-circle text-success mr-1"
                            ></i>

                            No attendance needs review.

                        </div>
                    `;

                    return;
                }


                /* Cards generate */
                box.innerHTML =
                    reviewItems.map(card).join('');
            }


            /* ==================================================
               LOAD ATTENDANCE REVIEW DATA

               Backend API:
               admin.attendance-reviews.feed

               Is route se JSON data aayega.
               ================================================== */

            async function load() {

                try {

                    const response = await fetch(
                        '{{ route("admin.attendance-reviews.feed") }}',
                        {
                            headers: {
                                Accept: 'application/json'
                            }
                        }
                    );


                    const data = await response.json();


                    /*
                     * Backend se items array receive
                     * karke local array mein save.
                     */
                    reviewItems = data.items || [];


                    /* UI update */
                    render();

                } catch (error) {

                    console.error(
                        'Attendance review feed error:',
                        error
                    );


                    document.getElementById(
                        'reviewCards'
                    ).innerHTML = `
                        <div class="review-empty">
                            Review feed could not be loaded.
                        </div>
                    `;
                }
            }


            /* ==================================================
               OPEN REVIEW LIST MODAL

               Parameter:
               - in_review
               - suspicious

               Sirf selected status ke records show honge.
               ================================================== */

            window.openReviewList = status => {

                const filtered =
                    reviewItems.filter(
                        item => item.status === status
                    );


                /* Modal title */
                document.getElementById(
                    'reviewListTitle'
                ).textContent =
                    status === 'in_review'
                        ? 'Attendance In Review'
                        : 'Suspicious Attendance';


                /* Cards */
                document.getElementById(
                    'reviewListBody'
                ).innerHTML =

                    filtered.length

                        ? filtered.map(card).join('')

                        : `
                            <div class="review-empty">
                                No records in this status.
                            </div>
                        `;


                /* Bootstrap modal open */
                $('#reviewListModal').modal('show');
            };


            /* ==================================================
               SHOW ATTENDANCE DETAILS

               Selected attendance ka detailed information
               Investigation modal mein show karta hai.
               ================================================== */

            window.showAttendanceReview = id => {

                /* Selected record find */
                const item =
                    reviewItems.find(
                        record => record.id === id
                    );


                if (!item) {
                    return;
                }


                /* =================================================
                   SUSPICIOUS RECORD KE CONTROLS

                   Suspicious record mein:
                   - Reason required
                   - Approve
                   - Reject
                   - Keep Suspicious
                   ================================================= */

                const controls =

                    item.status === 'suspicious'

                        ? `
                            <div class="form-group">

                                <label>
                                    Late approval reason
                                    <span class="text-danger">*</span>
                                </label>

                                <textarea
                                    id="reviewAdminNote"
                                    class="form-control"
                                    rows="3"
                                    placeholder="Enter why this late attendance is being approved"
                                ></textarea>

                                <small
                                    id="reviewNoteError"
                                    class="text-danger d-none"
                                >
                                    Late approval reason is required.
                                </small>

                            </div>


                            <button
                                class="btn btn-success mr-2"
                                onclick="window.setAttendanceReview(
                                    ${item.id},
                                    'approve'
                                )"
                            >
                                Approve Attendance
                            </button>


                            <button
                                class="btn btn-danger mr-2"
                                onclick="window.setAttendanceReview(
                                    ${item.id},
                                    'reject'
                                )"
                            >
                                Reject / Mark Absent
                            </button>


                            <button
                                class="btn btn-outline-danger"
                                onclick="window.setAttendanceReview(
                                    ${item.id},
                                    'suspicious'
                                )"
                            >
                                Keep Suspicious
                            </button>
                        `

                        : `
                            <div class="alert alert-warning mb-0">

                                <i class="fas fa-clock mr-1"></i>

                                This attendance is still in review.
                                Approval will be available after
                                the timer expires.

                            </div>
                        `;


                /* =================================================
                   DETAILS MODAL HTML
                   ================================================= */

                document.getElementById(
                    'reviewDetailBody'
                ).innerHTML = `

                    <div class="row">

                        {{-- LEFT COLUMN --}}
                        <div class="col-md-6">

                            <b>Employee</b>

                            <p>
                                ${esc(item.employee)}
                                (${esc(item.employee_code)})
                            </p>


                            <b>Punch time</b>

                            <p>
                                ${esc(item.punch_time)}
                            </p>


                            <b>Punch location</b>

                            <p>
                                ${esc(
                                    item.punch_location
                                    || 'Address unavailable'
                                )}
                            </p>

                        </div>


                        {{-- RIGHT COLUMN --}}
                        <div class="col-md-6">

                            <b>Office area</b>

                            <p>
                                ${esc(
                                    item.area
                                    || 'Nearest area'
                                )}
                                -
                                ${esc(item.radius)} m
                            </p>


                            <b>Punch distance</b>

                            <p>
                                ${esc(item.distance)} m
                            </p>


                            <b>Latest distance</b>

                            <p>
                                ${esc(
                                    item.latest_distance ?? 'N/A'
                                )} m
                            </p>


                            <b>Reached office area</b>

                            <p>
                                ${esc(
                                    item.entered_at
                                    || 'Not reached yet'
                                )}
                            </p>


                            <b>Arrival delay</b>

                            <p>
                                ${
                                    item.entered_at
                                        ? clock(
                                            Number(
                                                item.arrival_delay_seconds
                                                || 0
                                            )
                                        )
                                        : 'Still tracking'
                                }
                            </p>


                            {{-- Google Maps Location --}}
                            <a
                                class="btn btn-sm btn-outline-primary"
                                target="_blank"
                                href="https://www.google.com/maps?q=${encodeURIComponent(
                                    item.punch_latitude
                                    + ','
                                    + item.punch_longitude
                                )}"
                            >
                                <i class="fas fa-map-marker-alt mr-1"></i>
                                Open punch location
                            </a>

                        </div>

                    </div>


                    <hr>


                    {{-- System Review Reason --}}
                    <div class="review-info mb-3">

                        <b>System review:</b>

                        ${esc(
                            item.reason
                            || 'Outside configured office area'
                        )}

                    </div>


                    {{-- Approve / Reject controls --}}
                    ${controls}

                `;


                /* Agar list modal open tha to close */
                $('#reviewListModal').modal('hide');


                /* Details modal open */
                $('#reviewDetailModal').modal('show');
            };


            /* ==================================================
               UPDATE ATTENDANCE REVIEW STATUS

               Status:
               - approve
               - reject
               - suspicious

               Backend PATCH request send hoti hai.
               ================================================== */

            window.setAttendanceReview = async (
                id,
                status
            ) => {

                /* Admin note */
                const note =
                    document.getElementById(
                        'reviewAdminNote'
                    )?.value.trim() || '';


                const error =
                    document.getElementById(
                        'reviewNoteError'
                    );


                /* =================================================
                   APPROVE KARNE KE LIYE NOTE REQUIRED
                   ================================================= */

                if (status === 'approve' && !note) {

                    error?.classList.remove('d-none');

                    document
                        .getElementById('reviewAdminNote')
                        ?.focus();

                    return;
                }


                error?.classList.add('d-none');


                /* =================================================
                   UPDATE ROUTE

                   Laravel route mein __ID__ placeholder ko
                   actual attendance ID se replace kiya ja raha hai.
                   ================================================= */

                const url =
                    '{{ route(
                        "admin.attendance-reviews.update",
                        "__ID__"
                    ) }}'.replace(
                        '__ID__',
                        id
                    );


                /* =================================================
                   BACKEND PATCH REQUEST
                   ================================================= */

                const response = await fetch(
                    url,
                    {
                        method: 'PATCH',

                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',

                            /* Laravel CSRF protection */
                            'X-CSRF-TOKEN':
                                '{{ csrf_token() }}'
                        },

                        body: JSON.stringify({
                            status: status,
                            review_note: note
                        })
                    }
                );


                /* JSON response */
                const data =
                    await response.json();


                /* =================================================
                   SUCCESS
                   ================================================= */

                if (response.ok) {

                    /* Close modals */
                    $('#reviewDetailModal').modal('hide');
                    $('#reviewListModal').modal('hide');


                    /* Fresh API data */
                    await load();


                    /*
                     * Complete page reload.
                     * Isse dashboard ke baaki attendance
                     * related sections bhi refresh ho jayenge.
                     */
                    window.location.reload();

                    return;
                }


                /* =================================================
                   ERROR
                   ================================================= */

                alert(
                    data.message
                    || Object.values(
                        data.errors || {}
                    ).flat().join('\n')
                    || 'Unable to update attendance.'
                );
            };


            /* ==================================================
               LIVE COUNTDOWN TIMER

               Har 1 second mein:
               .review-card.in_review ke deadline ko update karta hai.
               ================================================== */

            setInterval(
                () => {

                    document
                        .querySelectorAll(
                            '.review-card.in_review [data-deadline]'
                        )
                        .forEach(element => {

                            element.textContent =
                                clock(
                                    seconds(
                                        element.dataset.deadline
                                    )
                                );

                        });

                },
                1000
            );


            /* ==================================================
               AUTO REFRESH

               Har 15 seconds backend se latest attendance
               review data fetch karega.
               ================================================== */

            setInterval(
                load,
                15000
            );


            /* ==================================================
               INITIAL LOAD

               Page open hote hi API se data load.
               ================================================== */

            load();

        })();

    </script>

@endif
```
