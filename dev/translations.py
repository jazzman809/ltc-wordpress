"""Seed translations for the plugin's own strings (es, fr, pt, de, it).

Writes languages/<domain>-<locale>.po from the .pot; compile with
`dev/wp i18n make-mo` and `dev/wp i18n make-php`. translate.wordpress.org
takes over once the plugin is listed; these make all six languages complete
on day one.
"""
import pathlib
import re

DOMAIN = "lotalytics-lottery-results"
LANG = pathlib.Path(__file__).resolve().parent.parent / DOMAIN / "languages"

T = {
 "Lotalytics – Lottery Results": {"es": "Lotalytics – Resultados de lotería", "fr": "Lotalytics – Résultats de loterie", "pt": "Lotalytics – Resultados da lotaria", "de": "Lotalytics – Lottoergebnisse", "it": "Lotalytics – Risultati del lotto"},
 "Show the latest lottery results and the next draw date for 50+ lotteries, as blocks, a shortcode or a widget. Results come from the Lotalytics API.": {
   "es": "Muestra los últimos resultados y la fecha del próximo sorteo de más de 50 loterías, como bloques, shortcode o widget. Los resultados proceden de la API de Lotalytics.",
   "fr": "Affichez les derniers résultats et la date du prochain tirage de plus de 50 loteries, en blocs, shortcode ou widget. Les résultats proviennent de l’API Lotalytics.",
   "pt": "Mostre os últimos resultados e a data do próximo sorteio de mais de 50 lotarias, como blocos, shortcode ou widget. Os resultados vêm da API da Lotalytics.",
   "de": "Zeigt die neuesten Lottoergebnisse und das Datum der nächsten Ziehung für über 50 Lotterien als Blöcke, Shortcode oder Widget. Die Ergebnisse stammen aus der Lotalytics-API.",
   "it": "Mostra gli ultimi risultati e la data della prossima estrazione di oltre 50 lotterie, come blocchi, shortcode o widget. I risultati provengono dall’API di Lotalytics."},
 "The Lotalytics API answered HTTP %d.": {"es": "La API de Lotalytics respondió HTTP %d.", "fr": "L’API Lotalytics a répondu HTTP %d.", "pt": "A API da Lotalytics respondeu HTTP %d.", "de": "Die Lotalytics-API antwortete mit HTTP %d.", "it": "L’API di Lotalytics ha risposto HTTP %d."},
 "Settings": {"es": "Ajustes", "fr": "Réglages", "pt": "Definições", "de": "Einstellungen", "it": "Impostazioni"},
 "Game": {"es": "Juego", "fr": "Jeu", "pt": "Jogo", "de": "Spiel", "it": "Gioco"},
 "Number of draws": {"es": "Número de sorteos", "fr": "Nombre de tirages", "pt": "Número de sorteios", "de": "Anzahl der Ziehungen", "it": "Numero di estrazioni"},
 "Compact (hide the next draw date)": {"es": "Compacto (oculta la fecha del próximo sorteo)", "fr": "Compact (masque la date du prochain tirage)", "pt": "Compacto (oculta a data do próximo sorteio)", "de": "Kompakt (ohne Datum der nächsten Ziehung)", "it": "Compatto (nasconde la data della prossima estrazione)"},
 "Show the title": {"es": "Mostrar el título", "fr": "Afficher le titre", "pt": "Mostrar o título", "de": "Titel anzeigen", "it": "Mostra il titolo"},
 'The game list could not be loaded. Type a game slug, for example \\"powerball\\".': {
   "es": 'No se pudo cargar la lista de juegos. Escribe el slug de un juego, por ejemplo \\"powerball\\".',
   "fr": 'La liste des jeux n’a pas pu être chargée. Saisissez le slug d’un jeu, par exemple \\"powerball\\".',
   "pt": 'Não foi possível carregar a lista de jogos. Escreva o slug de um jogo, por exemplo \\"powerball\\".',
   "de": 'Die Spieleliste konnte nicht geladen werden. Geben Sie den Slug eines Spiels ein, zum Beispiel \\"powerball\\".',
   "it": 'Non è stato possibile caricare l’elenco dei giochi. Scrivi lo slug di un gioco, ad esempio \\"powerball\\".'},
 "Game slug": {"es": "Slug del juego", "fr": "Slug du jeu", "pt": "Slug do jogo", "de": "Spiel-Slug", "it": "Slug del gioco"},
 "%s results by Lotalytics": {"es": "Resultados de %s por Lotalytics", "fr": "Résultats %s par Lotalytics", "pt": "Resultados do %s pela Lotalytics", "de": "%s Ergebnisse von Lotalytics", "it": "Risultati %s da Lotalytics"},
 "Lottery results are unavailable right now. Check the game in the block settings, or try again in a few minutes.": {
   "es": "Los resultados no están disponibles ahora mismo. Revisa el juego en los ajustes del bloque o inténtalo de nuevo en unos minutos.",
   "fr": "Les résultats sont indisponibles pour le moment. Vérifiez le jeu dans les réglages du bloc ou réessayez dans quelques minutes.",
   "pt": "Os resultados não estão disponíveis neste momento. Verifique o jogo nas definições do bloco ou tente novamente dentro de alguns minutos.",
   "de": "Die Ergebnisse sind gerade nicht verfügbar. Prüfen Sie das Spiel in den Blockeinstellungen oder versuchen Sie es in ein paar Minuten erneut.",
   "it": "I risultati non sono disponibili in questo momento. Controlla il gioco nelle impostazioni del blocco o riprova tra qualche minuto."},
 "Lotalytics Lottery Results": {"es": "Resultados de lotería de Lotalytics", "fr": "Résultats de loterie Lotalytics", "pt": "Resultados da lotaria Lotalytics", "de": "Lotalytics Lottoergebnisse", "it": "Risultati del lotto Lotalytics"},
 "Credit link": {"es": "Enlace de crédito", "fr": "Lien de crédit", "pt": "Ligação de crédito", "de": "Quellenlink", "it": "Link di attribuzione"},
 "Default game": {"es": "Juego predeterminado", "fr": "Jeu par défaut", "pt": "Jogo predefinido", "de": "Standardspiel", "it": "Gioco predefinito"},
 "Styling": {"es": "Estilo", "fr": "Style", "pt": "Estilo", "de": "Gestaltung", "it": "Stile"},
 'Show \\"Results by Lotalytics\\" under the results': {"es": 'Mostrar \\"Resultados por Lotalytics\\" bajo los resultados', "fr": 'Afficher \\"Résultats par Lotalytics\\" sous les résultats', "pt": 'Mostrar \\"Resultados pela Lotalytics\\" sob os resultados', "de": '\\"Ergebnisse von Lotalytics\\" unter den Ergebnissen anzeigen', "it": 'Mostra \\"Risultati da Lotalytics\\" sotto i risultati'},
 "Off unless you turn it on. The free Lotalytics service asks sites that use it to show this credit; see the terms linked below.": {
   "es": "Desactivado salvo que lo actives. El servicio gratuito de Lotalytics pide a los sitios que lo usan que muestren este crédito; consulta los términos enlazados abajo.",
   "fr": "Désactivé tant que vous ne l’activez pas. Le service gratuit Lotalytics demande aux sites qui l’utilisent d’afficher ce crédit ; voir les conditions ci-dessous.",
   "pt": "Desativado a menos que o ative. O serviço gratuito da Lotalytics pede aos sites que o usam que mostrem este crédito; consulte os termos ligados abaixo.",
   "de": "Aus, solange Sie es nicht einschalten. Der kostenlose Lotalytics-Dienst bittet Websites, die ihn nutzen, diesen Hinweis anzuzeigen; siehe die unten verlinkten Bedingungen.",
   "it": "Disattivato finché non lo attivi. Il servizio gratuito di Lotalytics chiede ai siti che lo usano di mostrare questa attribuzione; vedi i termini collegati qui sotto."},
 'The game list could not be loaded. Enter a game slug, for example \\"powerball\\".': {
   "es": 'No se pudo cargar la lista de juegos. Introduce el slug de un juego, por ejemplo \\"powerball\\".',
   "fr": 'La liste des jeux n’a pas pu être chargée. Saisissez le slug d’un jeu, par exemple \\"powerball\\".',
   "pt": 'Não foi possível carregar a lista de jogos. Introduza o slug de um jogo, por exemplo \\"powerball\\".',
   "de": 'Die Spieleliste konnte nicht geladen werden. Geben Sie den Slug eines Spiels ein, zum Beispiel \\"powerball\\".',
   "it": 'Non è stato possibile caricare l’elenco dei giochi. Inserisci lo slug di un gioco, ad esempio \\"powerball\\".'},
 "Disable the plugin stylesheet": {"es": "Desactivar la hoja de estilos del plugin", "fr": "Désactiver la feuille de style de l’extension", "pt": "Desativar a folha de estilos do plugin", "de": "Stylesheet des Plugins deaktivieren", "it": "Disattiva il foglio di stile del plugin"},
 "For themes that style the results themselves. The markup uses ltc- class names.": {"es": "Para temas que dan estilo a los resultados por su cuenta. El marcado usa clases ltc-.", "fr": "Pour les thèmes qui stylisent eux-mêmes les résultats. Le balisage utilise des classes ltc-.", "pt": "Para temas que estilizam os resultados por conta própria. A marcação usa classes ltc-.", "de": "Für Themes, die die Ergebnisse selbst gestalten. Das Markup nutzt ltc--Klassen.", "it": "Per i temi che stilizzano i risultati da soli. Il markup usa classi ltc-."},
 "Cache cleared.": {"es": "Caché vaciada.", "fr": "Cache vidé.", "pt": "Cache limpa.", "de": "Cache geleert.", "it": "Cache svuotata."},
 'Add the \\"Lottery results\\" or \\"Next lottery draw\\" block to any page, or use a shortcode:': {
   "es": 'Añade el bloque \\"Resultados de lotería\\" o \\"Próximo sorteo\\" a cualquier página, o usa un shortcode:',
   "fr": 'Ajoutez le bloc \\"Résultats de loterie\\" ou \\"Prochain tirage\\" à n’importe quelle page, ou utilisez un shortcode :',
   "pt": 'Adicione o bloco \\"Resultados da lotaria\\" ou \\"Próximo sorteio\\" a qualquer página, ou use um shortcode:',
   "de": 'Fügen Sie den Block \\"Lottoergebnisse\\" oder \\"Nächste Ziehung\\" in eine beliebige Seite ein oder verwenden Sie einen Shortcode:',
   "it": 'Aggiungi il blocco \\"Risultati del lotto\\" o \\"Prossima estrazione\\" a qualsiasi pagina, oppure usa uno shortcode:'},
 "Clear cached results": {"es": "Vaciar los resultados en caché", "fr": "Vider les résultats en cache", "pt": "Limpar os resultados em cache", "de": "Zwischengespeicherte Ergebnisse löschen", "it": "Svuota i risultati in cache"},
 "About the data": {"es": "Sobre los datos", "fr": "À propos des données", "pt": "Sobre os dados", "de": "Über die Daten", "it": "Informazioni sui dati"},
 "This plugin is a front end for the Lotalytics service. Your server requests results from api.lotalytics.com and caches them; your visitors' browsers never contact Lotalytics. Each request sends the game, the number of draws, the page language and your site's address. Nothing about your visitors is sent.": {
   "es": "Este plugin es una interfaz del servicio Lotalytics. Tu servidor pide los resultados a api.lotalytics.com y los guarda en caché; los navegadores de tus visitantes nunca contactan con Lotalytics. Cada petición envía el juego, el número de sorteos, el idioma de la página y la dirección de tu sitio. No se envía nada sobre tus visitantes.",
   "fr": "Cette extension est une interface du service Lotalytics. Votre serveur demande les résultats à api.lotalytics.com et les met en cache ; les navigateurs de vos visiteurs ne contactent jamais Lotalytics. Chaque requête envoie le jeu, le nombre de tirages, la langue de la page et l’adresse de votre site. Rien sur vos visiteurs n’est envoyé.",
   "pt": "Este plugin é uma interface do serviço Lotalytics. O seu servidor pede os resultados a api.lotalytics.com e guarda-os em cache; os navegadores dos seus visitantes nunca contactam a Lotalytics. Cada pedido envia o jogo, o número de sorteios, o idioma da página e o endereço do seu site. Nada sobre os seus visitantes é enviado.",
   "de": "Dieses Plugin ist eine Oberfläche für den Lotalytics-Dienst. Ihr Server ruft die Ergebnisse von api.lotalytics.com ab und speichert sie zwischen; die Browser Ihrer Besucher kontaktieren Lotalytics nie. Jede Anfrage übermittelt das Spiel, die Anzahl der Ziehungen, die Seitensprache und die Adresse Ihrer Website. Über Ihre Besucher wird nichts übermittelt.",
   "it": "Questo plugin è un’interfaccia del servizio Lotalytics. Il tuo server richiede i risultati ad api.lotalytics.com e li mette in cache; i browser dei tuoi visitatori non contattano mai Lotalytics. Ogni richiesta invia il gioco, il numero di estrazioni, la lingua della pagina e l’indirizzo del tuo sito. Non viene inviato nulla sui tuoi visitatori."},
 "Terms of service": {"es": "Términos del servicio", "fr": "Conditions d’utilisation", "pt": "Termos de serviço", "de": "Nutzungsbedingungen", "it": "Termini di servizio"},
 "Privacy policy": {"es": "Política de privacidad", "fr": "Politique de confidentialité", "pt": "Política de privacidade", "de": "Datenschutzerklärung", "it": "Informativa sulla privacy"},
 "You are not allowed to do that.": {"es": "No tienes permiso para hacer eso.", "fr": "Vous n’avez pas l’autorisation de faire cela.", "pt": "Não tem permissão para fazer isso.", "de": "Das dürfen Sie nicht.", "it": "Non hai il permesso di farlo."},
 "Lotalytics API": {"es": "API de Lotalytics", "fr": "API Lotalytics", "pt": "API da Lotalytics", "de": "Lotalytics-API", "it": "API di Lotalytics"},
 "Lottery results can be loaded from Lotalytics": {"es": "Los resultados de lotería se pueden cargar desde Lotalytics", "fr": "Les résultats de loterie peuvent être chargés depuis Lotalytics", "pt": "Os resultados da lotaria podem ser carregados a partir da Lotalytics", "de": "Lottoergebnisse können von Lotalytics geladen werden", "it": "I risultati del lotto possono essere caricati da Lotalytics"},
 "Lottery results could not be loaded from Lotalytics": {"es": "No se pudieron cargar los resultados de lotería desde Lotalytics", "fr": "Les résultats de loterie n’ont pas pu être chargés depuis Lotalytics", "pt": "Não foi possível carregar os resultados da lotaria a partir da Lotalytics", "de": "Lottoergebnisse konnten nicht von Lotalytics geladen werden", "it": "Non è stato possibile caricare i risultati del lotto da Lotalytics"},
 "The plugin reached api.lotalytics.com. Results are cached, and the last good copy is shown if the service is briefly unavailable.": {
   "es": "El plugin conectó con api.lotalytics.com. Los resultados se guardan en caché y, si el servicio no está disponible un momento, se muestra la última copia válida.",
   "fr": "L’extension a joint api.lotalytics.com. Les résultats sont mis en cache, et la dernière copie valide est affichée si le service est brièvement indisponible.",
   "pt": "O plugin contactou api.lotalytics.com. Os resultados ficam em cache e, se o serviço estiver brevemente indisponível, é mostrada a última cópia válida.",
   "de": "Das Plugin hat api.lotalytics.com erreicht. Die Ergebnisse werden zwischengespeichert; ist der Dienst kurz nicht erreichbar, wird die letzte gültige Kopie angezeigt.",
   "it": "Il plugin ha raggiunto api.lotalytics.com. I risultati sono in cache e, se il servizio non è disponibile per poco, viene mostrata l’ultima copia valida."},
 "The game list came back empty.": {"es": "La lista de juegos llegó vacía.", "fr": "La liste des jeux est revenue vide.", "pt": "A lista de jogos veio vazia.", "de": "Die Spieleliste war leer.", "it": "L’elenco dei giochi è arrivato vuoto."},
 "Lottery results (Lotalytics)": {"es": "Resultados de lotería (Lotalytics)", "fr": "Résultats de loterie (Lotalytics)", "pt": "Resultados da lotaria (Lotalytics)", "de": "Lottoergebnisse (Lotalytics)", "it": "Risultati del lotto (Lotalytics)"},
 "The latest winning numbers of a lottery.": {"es": "Los últimos números ganadores de una lotería.", "fr": "Les derniers numéros gagnants d’une loterie.", "pt": "Os últimos números premiados de uma lotaria.", "de": "Die neuesten Gewinnzahlen einer Lotterie.", "it": "Gli ultimi numeri vincenti di una lotteria."},
 "Title (optional)": {"es": "Título (opcional)", "fr": "Titre (facultatif)", "pt": "Título (opcional)", "de": "Titel (optional)", "it": "Titolo (facoltativo)"},
 "Bonus": {"es": "Adicional", "fr": "Bonus", "pt": "Extra", "de": "Zusatzzahl", "it": "Extra"},
 "%s results": {"es": "Resultados de %s", "fr": "Résultats %s", "pt": "Resultados do %s", "de": "%s Ergebnisse", "it": "Risultati %s"},
 "Winning numbers": {"es": "Números ganadores", "fr": "Numéros gagnants", "pt": "Números premiados", "de": "Gewinnzahlen", "it": "Numeri vincenti"},
 "Next draw: %s": {"es": "Próximo sorteo: %s", "fr": "Prochain tirage : %s", "pt": "Próximo sorteio: %s", "de": "Nächste Ziehung: %s", "it": "Prossima estrazione: %s"},
 "Next %s draw": {"es": "Próximo sorteo de %s", "fr": "Prochain tirage %s", "pt": "Próximo sorteio do %s", "de": "Nächste %s Ziehung", "it": "Prossima estrazione %s"},
 ("block title", "Lottery results"): {"es": "Resultados de lotería", "fr": "Résultats de loterie", "pt": "Resultados da lotaria", "de": "Lottoergebnisse", "it": "Risultati del lotto"},
 ("block description", "The latest winning numbers of a lottery, with the next draw date."): {"es": "Los últimos números ganadores de una lotería, con la fecha del próximo sorteo.", "fr": "Les derniers numéros gagnants d’une loterie, avec la date du prochain tirage.", "pt": "Os últimos números premiados de uma lotaria, com a data do próximo sorteio.", "de": "Die neuesten Gewinnzahlen einer Lotterie, mit dem Datum der nächsten Ziehung.", "it": "Gli ultimi numeri vincenti di una lotteria, con la data della prossima estrazione."},
 ("block keyword", "lottery"): {"es": "lotería", "fr": "loterie", "pt": "lotaria", "de": "Lotterie", "it": "lotteria"},
 ("block keyword", "lotto"): {"es": "lotto", "fr": "loto", "pt": "loto", "de": "Lotto", "it": "lotto"},
 ("block keyword", "results"): {"es": "resultados", "fr": "résultats", "pt": "resultados", "de": "Ergebnisse", "it": "risultati"},
 ("block keyword", "winning numbers"): {"es": "números ganadores", "fr": "numéros gagnants", "pt": "números premiados", "de": "Gewinnzahlen", "it": "numeri vincenti"},
 ("block keyword", "powerball"): {"es": "powerball", "fr": "powerball", "pt": "powerball", "de": "powerball", "it": "powerball"},
 ("block title", "Next lottery draw"): {"es": "Próximo sorteo de lotería", "fr": "Prochain tirage de loterie", "pt": "Próximo sorteio da lotaria", "de": "Nächste Lottoziehung", "it": "Prossima estrazione del lotto"},
 ("block description", "The date of a lottery's next scheduled draw."): {"es": "La fecha del próximo sorteo programado de una lotería.", "fr": "La date du prochain tirage prévu d’une loterie.", "pt": "A data do próximo sorteio previsto de uma lotaria.", "de": "Das Datum der nächsten geplanten Ziehung einer Lotterie.", "it": "La data della prossima estrazione prevista di una lotteria."},
 ("block keyword", "next draw"): {"es": "próximo sorteo", "fr": "prochain tirage", "pt": "próximo sorteio", "de": "nächste Ziehung", "it": "prossima estrazione"},
 ("block keyword", "jackpot"): {"es": "bote", "fr": "jackpot", "pt": "jackpot", "de": "Jackpot", "it": "jackpot"},
}
SKIP = {"https://lotalytics.com/widgets", "https://lotalytics.com", "Lotalytics"}
LOCALES = {"es_ES": "es", "es_MX": "es", "fr_FR": "fr", "pt_PT": "pt", "pt_BR": "pt", "de_DE": "de", "it_IT": "it"}
PLURAL = "nplurals=2; plural=(n != 1);"

pot = (LANG / f"{DOMAIN}.pot").read_text()
entries = re.findall(r'(?:msgctxt "([^"]*)"\n)?msgid "((?:[^"\\]|\\.)*)"', pot)
missing = []
for wp_locale, lang in LOCALES.items():
    out = [f'msgid ""\nmsgstr ""\n"Project-Id-Version: Lotalytics – Lottery Results 0.1.0\\n"\n"Language: {wp_locale}\\n"\n'
           f'"MIME-Version: 1.0\\n"\n"Content-Type: text/plain; charset=UTF-8\\n"\n"Content-Transfer-Encoding: 8bit\\n"\n'
           f'"Plural-Forms: {PLURAL}\\n"\n"X-Domain: {DOMAIN}\\n"\n']
    for ctx, mid in entries:
        if not mid or mid in SKIP:
            continue
        tr = T.get((ctx, mid)) if ctx else T.get(mid)
        if tr is None:
            missing.append((ctx, mid)); continue
        block = (f'msgctxt "{ctx}"\n' if ctx else "") + f'msgid "{mid}"\nmsgstr "{tr[lang]}"\n'
        out.append(block)
    (LANG / f"{DOMAIN}-{wp_locale}.po").write_text("\n".join(out))
print("missing:", sorted(set(missing)))
