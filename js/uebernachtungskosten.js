(function ($) {
  $(document).ready(function () {
    // Versteckte Eingabefelder für die tatsächlichen Anreise- und Abreisedaten (ISO-Format)
    const arrivalDateInput = $('input[name="acf[field_613f3a65c2bb6]"]');
    const departureDateInput = $('input[name="acf[field_613f3a77c2bb7]"]');

    // Greife auf die sichtbaren Datepicker-Felder zu
    const visibleDatepickers = $('.hasDatepicker');

    if (visibleDatepickers.length) {
      // Füge den `change`-Event-Listener für beide Felder hinzu
      visibleDatepickers.on('change', function () {
        // Werte der versteckten Felder überprüfen
        validateDates();
      });
    }

    function validateDates() {
      // Datumswerte umformatieren von yyyyMMdd nach yyyy-mm-dd
      const arrivalDateFormatted = formatDate(arrivalDateInput.val());
      const departureDateFormatted = formatDate(departureDateInput.val());

      const arrivalDate = new Date(arrivalDateFormatted);
      const departureDate = new Date(departureDateFormatted);

      // Überprüfung, ob das Abreisedatum leer ist
      if (!departureDateFormatted) {
        return;
      }

      // Überprüfung, ob das Abreisedatum vor dem Anreisedatum liegt
      if (departureDate < arrivalDate) {
        alert('Das Abreisedatum darf nicht vor dem Anreisedatum liegen.');

        // Setze das Abreisedatum im versteckten Feld und Datepicker zurück
        departureDateInput.val(''); // Setze das versteckte Abreisedatum zurück

        // Leere auch das sichtbare Datepicker-Feld
        const departureDateVisible = visibleDatepickers.eq(1); // Abreisedatum-Field
        departureDateVisible.val(''); // Sichtbares Feld leeren
      }

      // Falls der Benutzer das Anreisedatum geändert hat und das neue Anreisedatum vor dem bereits ausgewählten Abreisedatum liegt
      if (arrivalDate > departureDate) {
        // Lösche das Abreisedatum, da es nicht mehr korrekt ist
        departureDateInput.val(''); // Setze das versteckte Abreisedatum zurück

        // Leere auch das sichtbare Datepicker-Feld
        const departureDateVisible = visibleDatepickers.eq(1); // Abreisedatum-Field
        departureDateVisible.val(''); // Sichtbares Feld leeren
      }
    }

    // Hilfsfunktion, um das Datumsformat von yyyyMMdd nach yyyy-mm-dd zu ändern
    function formatDate(dateStr) {
      if (dateStr && dateStr.length === 8) {
        // Umwandeln von 20240901 nach 2024-09-01
        return (
          dateStr.slice(0, 4) +
          '-' +
          dateStr.slice(4, 6) +
          '-' +
          dateStr.slice(6, 8)
        );
      }
      return null;
    }
  });
})(jQuery);
