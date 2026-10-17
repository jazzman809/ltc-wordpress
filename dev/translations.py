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
T2 = {
 "Show the latest lottery results and the next draw date for 50+ lotteries, as blocks, a shortcode or a widget. With a Lotalytics API key: a results archive and a page per draw on your site.": {
   "es": "Muestra los últimos resultados y la fecha del próximo sorteo de más de 50 loterías, como bloques, shortcode o widget. Con una clave de la API de Lotalytics: un archivo de resultados y una página por sorteo en tu sitio.",
   "fr": "Affichez les derniers résultats et la date du prochain tirage de plus de 50 loteries, en blocs, shortcode ou widget. Avec une clé API Lotalytics : des archives de résultats et une page par tirage sur votre site.",
   "pt": "Mostre os últimos resultados e a data do próximo sorteio de mais de 50 lotarias, como blocos, shortcode ou widget. Com uma chave da API Lotalytics: um arquivo de resultados e uma página por sorteio no seu site.",
   "de": "Zeigt die neuesten Lottoergebnisse und das Datum der nächsten Ziehung für über 50 Lotterien als Blöcke, Shortcode oder Widget. Mit einem Lotalytics-API-Schlüssel: ein Ergebnisarchiv und eine Seite pro Ziehung auf Ihrer Website.",
   "it": "Mostra gli ultimi risultati e la data della prossima estrazione di oltre 50 lotterie, come blocchi, shortcode o widget. Con una chiave API Lotalytics: un archivio dei risultati e una pagina per estrazione sul tuo sito."},
 "%1$s numbers for %2$s": {"es": "Números de %1$s del %2$s", "fr": "Numéros %1$s du %2$s", "pt": "Números do %1$s de %2$s", "de": "%1$s Zahlen vom %2$s", "it": "Numeri %1$s del %2$s"},
 "%1$s of %2$s": {"es": "%1$s de %2$s", "fr": "%1$s sur %2$s", "pt": "%1$s de %2$s", "de": "%1$s von %2$s", "it": "%1$s su %2$s"},
 "%1$s winning numbers for %2$s: %3$s.": {"es": "Números ganadores de %1$s del %2$s: %3$s.", "fr": "Numéros gagnants %1$s du %2$s : %3$s.", "pt": "Números premiados do %1$s de %2$s: %3$s.", "de": "%1$s Gewinnzahlen vom %2$s: %3$s.", "it": "Numeri vincenti %1$s del %2$s: %3$s."},
 "%s results and winning numbers": {"es": "Resultados y números ganadores de %s", "fr": "Résultats et numéros gagnants %s", "pt": "Resultados e números premiados do %s", "de": "%s Ergebnisse und Gewinnzahlen", "it": "Risultati e numeri vincenti %s"},
 "API key": {"es": "Clave de API", "fr": "Clé API", "pt": "Chave de API", "de": "API-Schlüssel", "it": "Chiave API"},
 "Add an API key first.": {"es": "Añade primero una clave de API.", "fr": "Ajoutez d’abord une clé API.", "pt": "Adicione primeiro uma chave de API.", "de": "Fügen Sie zuerst einen API-Schlüssel hinzu.", "it": "Aggiungi prima una chiave API."},
 "Address:": {"es": "Dirección:", "fr": "Adresse :", "pt": "Endereço:", "de": "Adresse:", "it": "Indirizzo:"},
 "All %s results": {"es": "Todos los resultados de %s", "fr": "Tous les résultats %s", "pt": "Todos os resultados do %s", "de": "Alle %s Ergebnisse", "it": "Tutti i risultati %s"},
 "Check the key under Settings → Lotalytics. Until it works, the archive and the draw pages are off; the free results blocks keep working.": {
   "es": "Revisa la clave en Ajustes → Lotalytics. Hasta que funcione, el archivo y las páginas de sorteos están desactivados; los bloques gratuitos siguen funcionando.",
   "fr": "Vérifiez la clé dans Réglages → Lotalytics. Tant qu’elle ne fonctionne pas, les archives et les pages de tirage sont désactivées ; les blocs gratuits continuent de fonctionner.",
   "pt": "Verifique a chave em Definições → Lotalytics. Enquanto não funcionar, o arquivo e as páginas de sorteio ficam desativados; os blocos gratuitos continuam a funcionar.",
   "de": "Prüfen Sie den Schlüssel unter Einstellungen → Lotalytics. Bis er funktioniert, sind Archiv und Ziehungsseiten aus; die kostenlosen Blöcke funktionieren weiter.",
   "it": "Controlla la chiave in Impostazioni → Lotalytics. Finché non funziona, l’archivio e le pagine delle estrazioni sono disattivati; i blocchi gratuiti continuano a funzionare."},
 "Credit link found.": {"es": "Enlace de crédito encontrado.", "fr": "Lien de crédit trouvé.", "pt": "Ligação de crédito encontrada.", "de": "Quellenlink gefunden.", "it": "Link di attribuzione trovato."},
 "Credit link missing: the key is limited to a few requests a day.": {"es": "Falta el enlace de crédito: la clave está limitada a unas pocas solicitudes al día.", "fr": "Lien de crédit absent : la clé est limitée à quelques requêtes par jour.", "pt": "Falta a ligação de crédito: a chave está limitada a poucos pedidos por dia.", "de": "Quellenlink fehlt: Der Schlüssel ist auf wenige Anfragen pro Tag begrenzt.", "it": "Link di attribuzione mancante: la chiave è limitata a poche richieste al giorno."},
 "Credit link missing: the key will be limited soon.": {"es": "Falta el enlace de crédito: la clave se limitará pronto.", "fr": "Lien de crédit absent : la clé sera bientôt limitée.", "pt": "Falta a ligação de crédito: a chave será limitada em breve.", "de": "Quellenlink fehlt: Der Schlüssel wird bald begrenzt.", "it": "Link di attribuzione mancante: la chiave sarà presto limitata."},
 "Custom": {"es": "Personalizado", "fr": "Personnalisé", "pt": "Personalizado", "de": "Individuell", "it": "Personalizzato"},
 "Draws per page": {"es": "Sorteos por página", "fr": "Tirages par page", "pt": "Sorteios por página", "de": "Ziehungen pro Seite", "it": "Estrazioni per pagina"},
 "Draws per page:": {"es": "Sorteos por página:", "fr": "Tirages par page :", "pt": "Sorteios por página:", "de": "Ziehungen pro Seite:", "it": "Estrazioni per pagina:"},
 "Free plan credit link": {"es": "Enlace de crédito del plan gratuito", "fr": "Lien de crédit de l’offre gratuite", "pt": "Ligação de crédito do plano gratuito", "de": "Quellenlink des kostenlosen Tarifs", "it": "Link di attribuzione del piano gratuito"},
 "Games": {"es": "Juegos", "fr": "Jeux", "pt": "Jogos", "de": "Spiele", "it": "Giochi"},
 "Get or manage a key on lotalytics.com": {"es": "Obtén o gestiona una clave en lotalytics.com", "fr": "Obtenir ou gérer une clé sur lotalytics.com", "pt": "Obtenha ou gira uma chave em lotalytics.com", "de": "Schlüssel auf lotalytics.com holen oder verwalten", "it": "Ottieni o gestisci una chiave su lotalytics.com"},
 "Grace period: add the credit link soon.": {"es": "Periodo de gracia: añade pronto el enlace de crédito.", "fr": "Période de grâce : ajoutez bientôt le lien de crédit.", "pt": "Período de carência: adicione em breve a ligação de crédito.", "de": "Schonfrist: Fügen Sie bald den Quellenlink ein.", "it": "Periodo di prova: aggiungi presto il link di attribuzione."},
 "Latest lottery results and winning numbers.": {"es": "Últimos resultados y números ganadores de lotería.", "fr": "Derniers résultats et numéros gagnants de loterie.", "pt": "Últimos resultados e números premiados da lotaria.", "de": "Aktuelle Lottoergebnisse und Gewinnzahlen.", "it": "Ultimi risultati e numeri vincenti del lotto."},
 "Lotalytics API key": {"es": "Clave de la API de Lotalytics", "fr": "Clé API Lotalytics", "pt": "Chave da API Lotalytics", "de": "Lotalytics-API-Schlüssel", "it": "Chiave API di Lotalytics"},
 "Lotalytics didn't accept this key. Check it on the Developer page, or remove it.": {"es": "Lotalytics no aceptó esta clave. Revísala en la página de Desarrolladores o elimínala.", "fr": "Lotalytics n’a pas accepté cette clé. Vérifiez-la sur la page Développeurs ou supprimez-la.", "pt": "A Lotalytics não aceitou esta chave. Verifique-a na página de Programadores ou remova-a.", "de": "Lotalytics hat diesen Schlüssel nicht akzeptiert. Prüfen Sie ihn auf der Entwicklerseite oder entfernen Sie ihn.", "it": "Lotalytics non ha accettato questa chiave. Controllala nella pagina Sviluppatori o rimuovila."},
 "Lottery results": {"es": "Resultados de lotería", "fr": "Résultats de loterie", "pt": "Resultados da lotaria", "de": "Lottoergebnisse", "it": "Risultati del lotto"},
 "Needs an API key whose plan includes the results archive and draw pages.": {"es": "Necesita una clave de API cuyo plan incluya el archivo de resultados y las páginas de sorteos.", "fr": "Nécessite une clé API dont l’offre inclut les archives et les pages de tirage.", "pt": "Precisa de uma chave de API cujo plano inclua o arquivo de resultados e as páginas de sorteio.", "de": "Benötigt einen API-Schlüssel, dessen Tarif Ergebnisarchiv und Ziehungsseiten enthält.", "it": "Serve una chiave API il cui piano includa l’archivio dei risultati e le pagine delle estrazioni."},
 "Newer draws": {"es": "Sorteos más recientes", "fr": "Tirages plus récents", "pt": "Sorteios mais recentes", "de": "Neuere Ziehungen", "it": "Estrazioni più recenti"},
 "Older draws": {"es": "Sorteos anteriores", "fr": "Tirages plus anciens", "pt": "Sorteios anteriores", "de": "Ältere Ziehungen", "it": "Estrazioni precedenti"},
 "Optional. Without a key the free results blocks work as they are. A key on a paid plan adds the results archive and a page per draw on your site. Leave blank to keep the current key; enter - to remove it.": {
   "es": "Opcional. Sin clave, los bloques gratuitos funcionan igual. Una clave de un plan de pago añade el archivo de resultados y una página por sorteo en tu sitio. Déjalo en blanco para conservar la clave actual; escribe - para eliminarla.",
   "fr": "Facultatif. Sans clé, les blocs gratuits fonctionnent tels quels. Une clé d’une offre payante ajoute les archives et une page par tirage sur votre site. Laissez vide pour garder la clé actuelle ; saisissez - pour la supprimer.",
   "pt": "Opcional. Sem chave, os blocos gratuitos funcionam como estão. Uma chave de um plano pago acrescenta o arquivo de resultados e uma página por sorteio no seu site. Deixe em branco para manter a chave atual; escreva - para a remover.",
   "de": "Optional. Ohne Schlüssel funktionieren die kostenlosen Blöcke wie bisher. Ein Schlüssel eines kostenpflichtigen Tarifs ergänzt Ihre Website um ein Ergebnisarchiv und eine Seite pro Ziehung. Leer lassen, um den aktuellen Schlüssel zu behalten; - eingeben, um ihn zu entfernen.",
   "it": "Facoltativa. Senza chiave i blocchi gratuiti funzionano come ora. Una chiave di un piano a pagamento aggiunge l’archivio dei risultati e una pagina per estrazione al tuo sito. Lascia vuoto per mantenere la chiave attuale; scrivi - per rimuoverla."},
 "Other draws": {"es": "Otros sorteos", "fr": "Autres tirages", "pt": "Outros sorteios", "de": "Weitere Ziehungen", "it": "Altre estrazioni"},
 "Page %1$d of %2$d": {"es": "Página %1$d de %2$d", "fr": "Page %1$d sur %2$d", "pt": "Página %1$d de %2$d", "de": "Seite %1$d von %2$d", "it": "Pagina %1$d di %2$d"},
 "Page %d": {"es": "Página %d", "fr": "Page %d", "pt": "Página %d", "de": "Seite %d", "it": "Pagina %d"},
 "Plan": {"es": "Plan", "fr": "Offre", "pt": "Plano", "de": "Tarif", "it": "Piano"},
 "Plan: %s.": {"es": "Plan: %s.", "fr": "Offre : %s.", "pt": "Plano: %s.", "de": "Tarif: %s.", "it": "Piano: %s."},
 "Publish a results section on this site": {"es": "Publicar una sección de resultados en este sitio", "fr": "Publier une rubrique de résultats sur ce site", "pt": "Publicar uma secção de resultados neste site", "de": "Einen Ergebnisbereich auf dieser Website veröffentlichen", "it": "Pubblica una sezione dei risultati su questo sito"},
 "Register this site": {"es": "Registrar este sitio", "fr": "Enregistrer ce site", "pt": "Registar este site", "de": "Diese Website registrieren", "it": "Registra questo sito"},
 "Register this site so Lotalytics can find the credit link here.": {"es": "Registra este sitio para que Lotalytics pueda encontrar aquí el enlace de crédito.", "fr": "Enregistrez ce site pour que Lotalytics y trouve le lien de crédit.", "pt": "Registe este site para que a Lotalytics encontre aqui a ligação de crédito.", "de": "Registrieren Sie diese Website, damit Lotalytics den Quellenlink hier findet.", "it": "Registra questo sito perché Lotalytics trovi qui il link di attribuzione."},
 "Requests this month": {"es": "Solicitudes este mes", "fr": "Requêtes ce mois-ci", "pt": "Pedidos este mês", "de": "Anfragen in diesem Monat", "it": "Richieste questo mese"},
 "Requests today": {"es": "Solicitudes hoy", "fr": "Requêtes aujourd’hui", "pt": "Pedidos hoje", "de": "Anfragen heute", "it": "Richieste oggi"},
 "Results archive and draw pages": {"es": "Archivo de resultados y páginas de sorteos", "fr": "Archives et pages de tirage", "pt": "Arquivo de resultados e páginas de sorteio", "de": "Ergebnisarchiv und Ziehungsseiten", "it": "Archivio dei risultati e pagine delle estrazioni"},
 "Results are cached for longer until the limit resets. A bigger plan raises the limit.": {"es": "Los resultados se guardan en caché más tiempo hasta que se renueve el límite. Un plan mayor amplía el límite.", "fr": "Les résultats sont mis en cache plus longtemps jusqu’à la remise à zéro de la limite. Une offre supérieure relève la limite.", "pt": "Os resultados ficam em cache mais tempo até o limite ser reposto. Um plano maior aumenta o limite.", "de": "Ergebnisse werden länger zwischengespeichert, bis das Limit zurückgesetzt wird. Ein größerer Tarif erhöht das Limit.", "it": "I risultati restano in cache più a lungo finché il limite non si azzera. Un piano superiore alza il limite."},
 "Results pages": {"es": "Páginas de resultados", "fr": "Pages de résultats", "pt": "Páginas de resultados", "de": "Ergebnisseiten", "it": "Pagine dei risultati"},
 "Results section": {"es": "Sección de resultados", "fr": "Rubrique de résultats", "pt": "Secção de resultados", "de": "Ergebnisbereich", "it": "Sezione dei risultati"},
 "Set in wp-config.php (LOTALYTICS_API_KEY).": {"es": "Definida en wp-config.php (LOTALYTICS_API_KEY).", "fr": "Définie dans wp-config.php (LOTALYTICS_API_KEY).", "pt": "Definida em wp-config.php (LOTALYTICS_API_KEY).", "de": "In wp-config.php gesetzt (LOTALYTICS_API_KEY).", "it": "Impostata in wp-config.php (LOTALYTICS_API_KEY)."},
 "Site registered.": {"es": "Sitio registrado.", "fr": "Site enregistré.", "pt": "Site registado.", "de": "Website registriert.", "it": "Sito registrato."},
 "Statistics": {"es": "Estadísticas", "fr": "Statistiques", "pt": "Estatísticas", "de": "Statistiken", "it": "Statistiche"},
 'That doesn\'t look like a Lotalytics API key (they start with \\"ltc_\\"). The previous key was kept.': {
   "es": 'Eso no parece una clave de API de Lotalytics (empiezan por \\"ltc_\\"). Se ha conservado la clave anterior.',
   "fr": 'Cela ne ressemble pas à une clé API Lotalytics (elles commencent par \\"ltc_\\"). La clé précédente a été conservée.',
   "pt": 'Isso não parece uma chave de API da Lotalytics (começam por \\"ltc_\\"). A chave anterior foi mantida.',
   "de": 'Das sieht nicht wie ein Lotalytics-API-Schlüssel aus (sie beginnen mit \\"ltc_\\"). Der bisherige Schlüssel wurde behalten.',
   "it": 'Non sembra una chiave API di Lotalytics (iniziano con \\"ltc_\\"). È stata mantenuta la chiave precedente.'},
 "The Lotalytics API key is close to its request limit": {"es": "La clave de la API de Lotalytics está cerca de su límite de solicitudes", "fr": "La clé API Lotalytics approche de sa limite de requêtes", "pt": "A chave da API Lotalytics está perto do limite de pedidos", "de": "Der Lotalytics-API-Schlüssel ist nahe an seinem Anfragelimit", "it": "La chiave API di Lotalytics è vicina al limite di richieste"},
 "The Lotalytics API key was not accepted": {"es": "La clave de la API de Lotalytics no fue aceptada", "fr": "La clé API Lotalytics n’a pas été acceptée", "pt": "A chave da API Lotalytics não foi aceite", "de": "Der Lotalytics-API-Schlüssel wurde nicht akzeptiert", "it": "La chiave API di Lotalytics non è stata accettata"},
 "The Lotalytics API key works": {"es": "La clave de la API de Lotalytics funciona", "fr": "La clé API Lotalytics fonctionne", "pt": "A chave da API Lotalytics funciona", "de": "Der Lotalytics-API-Schlüssel funktioniert", "it": "La chiave API di Lotalytics funziona"},
 "The latest %s results and winning numbers, draw by draw.": {"es": "Los últimos resultados y números ganadores de %s, sorteo a sorteo.", "fr": "Les derniers résultats et numéros gagnants %s, tirage par tirage.", "pt": "Os últimos resultados e números premiados do %s, sorteio a sorteio.", "de": "Die neuesten %s Ergebnisse und Gewinnzahlen, Ziehung für Ziehung.", "it": "Gli ultimi risultati e numeri vincenti %s, estrazione per estrazione."},
 "The results archive needs a Lotalytics API key whose plan includes it (Settings → Lotalytics).": {"es": "El archivo de resultados necesita una clave de la API de Lotalytics cuyo plan lo incluya (Ajustes → Lotalytics).", "fr": "Les archives nécessitent une clé API Lotalytics dont l’offre les inclut (Réglages → Lotalytics).", "pt": "O arquivo de resultados precisa de uma chave da API Lotalytics cujo plano o inclua (Definições → Lotalytics).", "de": "Das Ergebnisarchiv benötigt einen Lotalytics-API-Schlüssel, dessen Tarif es enthält (Einstellungen → Lotalytics).", "it": "L’archivio dei risultati richiede una chiave API di Lotalytics il cui piano lo includa (Impostazioni → Lotalytics)."},
 "This key is close to its request limit. The plugin is caching results for longer until the limit resets.": {"es": "Esta clave está cerca de su límite de solicitudes. El plugin guarda los resultados en caché más tiempo hasta que se renueve el límite.", "fr": "Cette clé approche de sa limite de requêtes. L’extension met les résultats en cache plus longtemps jusqu’à la remise à zéro.", "pt": "Esta chave está perto do limite de pedidos. O plugin guarda os resultados em cache mais tempo até o limite ser reposto.", "de": "Dieser Schlüssel ist nahe an seinem Anfragelimit. Das Plugin speichert Ergebnisse länger zwischen, bis das Limit zurückgesetzt wird.", "it": "Questa chiave è vicina al limite di richieste. Il plugin tiene i risultati in cache più a lungo finché il limite non si azzera."},
 "This site (%1$s) is registered; last check: %2$s.": {"es": "Este sitio (%1$s) está registrado; última comprobación: %2$s.", "fr": "Ce site (%1$s) est enregistré ; dernière vérification : %2$s.", "pt": "Este site (%1$s) está registado; última verificação: %2$s.", "de": "Diese Website (%1$s) ist registriert; letzte Prüfung: %2$s.", "it": "Questo sito (%1$s) è registrato; ultimo controllo: %2$s."},
 "Turn on the credit link below so the check finds it.": {"es": "Activa el enlace de crédito abajo para que la comprobación lo encuentre.", "fr": "Activez le lien de crédit ci-dessous pour que la vérification le trouve.", "pt": "Ative a ligação de crédito abaixo para que a verificação a encontre.", "de": "Schalten Sie unten den Quellenlink ein, damit die Prüfung ihn findet.", "it": "Attiva qui sotto il link di attribuzione perché il controllo lo trovi."},
 "View the results section": {"es": "Ver la sección de resultados", "fr": "Voir la rubrique de résultats", "pt": "Ver a secção de resultados", "de": "Ergebnisbereich ansehen", "it": "Vedi la sezione dei risultati"},
 "Your Lotalytics plan doesn't include this game. Pick one of your plan's games in the block settings.": {"es": "Tu plan de Lotalytics no incluye este juego. Elige uno de los juegos de tu plan en los ajustes del bloque.", "fr": "Votre offre Lotalytics n’inclut pas ce jeu. Choisissez un des jeux de votre offre dans les réglages du bloc.", "pt": "O seu plano Lotalytics não inclui este jogo. Escolha um dos jogos do seu plano nas definições do bloco.", "de": "Ihr Lotalytics-Tarif enthält dieses Spiel nicht. Wählen Sie in den Blockeinstellungen eines Ihrer Tarifspiele.", "it": "Il tuo piano Lotalytics non include questo gioco. Scegli uno dei giochi del tuo piano nelle impostazioni del blocco."},
 "all": {"es": "todos", "fr": "tous", "pt": "todos", "de": "alle", "it": "tutti"},
 "no": {"es": "no", "fr": "non", "pt": "não", "de": "nein", "it": "no"},
 "none chosen yet (choose them on the Developer page)": {"es": "ninguno elegido aún (elígelos en la página de Desarrolladores)", "fr": "aucun choisi pour l’instant (choisissez-les sur la page Développeurs)", "pt": "nenhum escolhido ainda (escolha-os na página de Programadores)", "de": "noch keine gewählt (auf der Entwicklerseite auswählen)", "it": "nessuno scelto ancora (sceglili nella pagina Sviluppatori)"},
 "pending": {"es": "pendiente", "fr": "en attente", "pt": "pendente", "de": "ausstehend", "it": "in attesa"},
 "yes": {"es": "sí", "fr": "oui", "pt": "sim", "de": "ja", "it": "sì"},
 ("block title", "Lottery results archive"): {"es": "Archivo de resultados de lotería", "fr": "Archives des résultats de loterie", "pt": "Arquivo de resultados da lotaria", "de": "Lottoergebnis-Archiv", "it": "Archivio dei risultati del lotto"},
 ("block description", "A lottery's past draws, page by page. Needs a Lotalytics API key whose plan includes the archive."): {"es": "Los sorteos anteriores de una lotería, página a página. Necesita una clave de la API de Lotalytics cuyo plan incluya el archivo.", "fr": "Les tirages passés d’une loterie, page par page. Nécessite une clé API Lotalytics dont l’offre inclut les archives.", "pt": "Os sorteios anteriores de uma lotaria, página a página. Precisa de uma chave da API Lotalytics cujo plano inclua o arquivo.", "de": "Die vergangenen Ziehungen einer Lotterie, Seite für Seite. Benötigt einen Lotalytics-API-Schlüssel, dessen Tarif das Archiv enthält.", "it": "Le estrazioni passate di una lotteria, pagina per pagina. Richiede una chiave API di Lotalytics il cui piano includa l’archivio."},
 ("block keyword", "archive"): {"es": "archivo", "fr": "archives", "pt": "arquivo", "de": "Archiv", "it": "archivio"},
 ("block keyword", "history"): {"es": "historial", "fr": "historique", "pt": "histórico", "de": "Verlauf", "it": "storico"},
}
T.update(T2)
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
