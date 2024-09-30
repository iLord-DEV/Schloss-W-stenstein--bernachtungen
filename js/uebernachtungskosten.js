(function ($) {
  console.log('Script wird geladen');

  $(document).ready(function () {
    console.log('Document ready');

    // Greife auf die sichtbaren Datepicker-Felder zu
    const arrivalDatepicker = $(
      '.acf-field-date-picker[data-name="arrival_date"] input.input'
    );
    const departureDatepicker = $(
      '.acf-field-date-picker[data-name="departure_date"] input.input'
    );

    console.log('Anreise-Datepicker gefunden:', arrivalDatepicker.length);
    console.log('Abreise-Datepicker gefunden:', departureDatepicker.length);

    if (arrivalDatepicker.length && departureDatepicker.length) {
      arrivalDatepicker.add(departureDatepicker).on('change', function () {
        console.log('Datepicker changed');
        console.log(
          'Geändertes Feld:',
          $(this).closest('.acf-field').data('name')
        );
        console.log('Neuer Wert:', $(this).val());
        validateDates();
      });
    } else {
      console.log('Datepicker nicht gefunden');
    }

    function validateDates() {
      console.log('validateDates aufgerufen');
      const arrivalDateStr = arrivalDatepicker.val();
      const departureDateStr = departureDatepicker.val();

      console.log('Anreise:', arrivalDateStr);
      console.log('Abreise:', departureDateStr);

      const arrivalDate = parseDate(arrivalDateStr);
      const departureDate = parseDate(departureDateStr);

      console.log('Parsed Anreise:', arrivalDate);
      console.log('Parsed Abreise:', departureDate);

      if (!departureDate) {
        console.log('Abreisedatum ist leer');
        return;
      }

      if (departureDate < arrivalDate) {
        console.log('Abreisedatum liegt vor Anreisedatum');
        alert('Das Abreisedatum darf nicht vor dem Anreisedatum liegen.');
        departureDatepicker.val('').trigger('change');
      }
    }

    function parseDate(dateStr) {
      console.log('Parsing date:', dateStr);
      if (!dateStr) return null;

      let parts = dateStr.split('/');
      if (parts.length === 3) {
        // Beachten Sie, dass wir hier '20' + parts[2] verwenden, um das Jahr vierstellig zu machen
        return new Date(20 + parts[2], parts[1] - 1, parts[0]);
      }

      console.log('Konnte Datum nicht parsen:', dateStr);
      return null;
    }
  });
})(jQuery);
