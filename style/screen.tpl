<!--section-start::SCREEN_CONTAINER-->
    <div class="screen_container" align="center">
        <b style="font-variant:small-caps;">l e i n w a n d</b><br>
        {..screen_room..}
    </div>
<!--section-end::SCREEN_CONTAINER-->

<!--section-start::SCREEN_LINE_CONTAINER-->
    {..seats..}<br clear="all">
<!--section-end::SCREEN_LINE_CONTAINER-->

<!--section-start::SCREEN_ELEMENT-->
    <div class="screen screenelement" title="Leinwand"></div>
<!--section-end::SCREEN_ELEMENT-->

<!--section-start::SPACE-->
    <div class="screen space"></div>
<!--section-end::SPACE-->

<!--section-start::FREE-->
    <label for="seat_{..number..}" class="screen seat pointer" title="frei">
        <input type="checkbox" name="seats[]" id="seat_{..number..}" value="{..number..}">
    </label>
<!--section-end::FREE-->

<!--section-start::SEAT_BLOCKED-->
    <div class="screen seat blocked" title="temporär gesperrt"></div>
<!--section-end::SEAT_BLOCKED-->

<!--section-start::SEAT_SOLD-->
    <div class="screen seat sold" title="verkauft"></div>
<!--section-end::SEAT_SOLD-->

<!--section-start::SEAT_RESERVATION-->
    <div class="screen seat reservation" title="reserviert"></div>
<!--section-end::SEAT_RESERVATION-->

<!--section-start::SEAT_WHEELCHAIR-->
    <label for="seat_{..number..}" class="screen seat wheelchair pointer" title="Rollstuhlplatz (frei)">
        <input type="checkbox" name="seats[]" id="seat_{..number..}" value="{..number..}">
    </label>
<!--section-end::SEAT_WHEELCHAIR-->