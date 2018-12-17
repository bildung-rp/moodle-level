# Das Moodle Plugin „Level“ – ein Moodle-Werkzeug für einen leichten Einstieg in Moodle (für Trainer!)

## Ziel des Level Plugins ist es ein Moodle-Werkzeug zu schaffen, das u.a.
* den Funktionsumfang für Trainer zu minimieren und damit den Einstieg zu erleichtern

**Voraussetzung:**
* Moodle 3.5+                             

## Installation:

Bei der Konfiguration des Level Plugins ist die Matrix der Zugriffssteuerung entsprechend einzustellen.
Vor allem bei welcher Aktivität die Sichtbarkeit eingestellt werden kann.

## Technische Dokumentation:
### Ordnerstruktur
* local/authoringcapability
* theme/boost_level
* user/field/authoringlevelmenu

**Einbau in ein anderes Theme**
In der config.php muss zunächst folgende Zeile enthalten sein.

$THEME->rendererfactory = 'theme_overridden_renderer_factory';

Im classes Ordner (falls noch nicht vorhanden, muss dieser angelegt werden)

Dort müssen die PHP Dateien core_course_renderer und core_renderer angelegt bzw. ergänzt werden.

### Für den Arbeitsplaner sind folgende Methoden relevant

* classes/output/core/course_renderer.php
  * public function course_modchooser
* classes/output/core_renderer.php
  * public function header
