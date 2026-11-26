// Importing a widget registers it (each file calls registerWidget at load).
// Order here does not matter — the registry sorts by each widget's `order` and
// groups by `column` ('hero' | 'main' | 'side').
import './GreetingHero';
import './FeedComposer';
import './Feed';
import './NeedsYou';
import './MyTeam';
import './TimeOff';
import './Announcements';
import './Celebrations';
import './AdminSummary';
