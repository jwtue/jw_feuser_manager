/**
 * Zufallspasswort-Knopf im Bearbeiten-Formular.
 *
 * Frueher steckte diese Logik als inline-onclick im content-Property eines
 * LabeledFluid-Elements. Das konnte nicht zuverlaessig funktionieren: Das Partial
 * rendert seinen Inhalt ueber v:render.inline, also als Fluid-Quelltext — die
 * geschweiften Klammern im JavaScript wurden dabei als Fluid-Ausdruecke gedeutet
 * und der Knopf verschwand. Deshalb liegt der Code jetzt hier, und das Markup
 * kommt aus einem eigenen Partial (PasswordGenerator.html).
 */
(function () {
	'use strict';

	// Ohne optisch verwechselbare Zeichen (0/O, 1/l/I)
	var ZEICHEN = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnopqrstuvwxyz23456789';
	var STANDARD_LAENGE = 12;

	/**
	 * Liefert eine gleichverteilte Zufallszahl 0..max-1.
	 *
	 * Nutzt crypto.getRandomValues statt Math.random (das fuer Passwoerter
	 * ungeeignet ist) und verwirft Werte oberhalb des groessten Vielfachen von
	 * max, damit kein Modulo-Bias entsteht.
	 */
	function zufallsZahl(max) {
		var krypto = window.crypto || window.msCrypto;
		if (!krypto || !krypto.getRandomValues) {
			// Sehr alte Browser: lieber unsicherer Zufall als kein Knopf
			return Math.floor(Math.random() * max);
		}
		var grenze = Math.floor(0xffffffff / max) * max;
		var puffer = new Uint32Array(1);
		var wert;
		do {
			krypto.getRandomValues(puffer);
			wert = puffer[0];
		} while (wert >= grenze);
		return wert % max;
	}

	function passwortErzeugen(laenge) {
		var pw = '';
		for (var i = 0; i < laenge; i++) {
			pw += ZEICHEN.charAt(zufallsZahl(ZEICHEN.length));
		}
		return pw;
	}

	function setzeWert(feld, wert) {
		if (!feld) {
			return;
		}
		feld.value = wert;
		// sichtbar machen, damit der Bearbeiter das Passwort notieren kann
		feld.type = 'text';
		// Falls andere Skripte auf Aenderungen lauschen
		feld.dispatchEvent(new Event('input', { bubbles: true }));
		feld.dispatchEvent(new Event('change', { bubbles: true }));
	}

	function aktivieren(knopf) {
		knopf.addEventListener('click', function (ereignis) {
			ereignis.preventDefault();

			var feldId = knopf.getAttribute('data-field-id');
			if (!feldId) {
				return;
			}
			var laenge = parseInt(knopf.getAttribute('data-length'), 10);
			if (!laenge || laenge < 4) {
				laenge = STANDARD_LAENGE;
			}

			var passwort = passwortErzeugen(laenge);
			setzeWert(document.getElementById(feldId), passwort);
			setzeWert(document.getElementById(feldId + '-confirmation'), passwort);
		});
	}

	function initialisieren() {
		var knoepfe = document.querySelectorAll('.jw-feuser-pwgen');
		for (var i = 0; i < knoepfe.length; i++) {
			aktivieren(knoepfe[i]);
		}
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initialisieren);
	} else {
		initialisieren();
	}
})();
