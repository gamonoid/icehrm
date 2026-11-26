import { t } from '../../../../../web/api/i18n';

/** Wizard steps: titles and one-line descriptions (keys match SetupService::STEPS). */
export function stepInfo(key) {
  switch (key) {
    case 'clean': return { title: t('Start clean'), description: t('Remove the sample data so only your company\'s information is left.') };
    case 'company': return { title: t('Company details'), description: t('Your company name, country and time zone.') };
    case 'structure': return { title: t('Offices and departments'), description: t('How your company is organised.') };
    case 'job_titles': return { title: t('Job titles'), description: t('The roles people have in your company.') };
    case 'employment': return { title: t('Employment types'), description: t('Full-time, part-time, contract and other ways people are employed.') };
    case 'calendar': return { title: t('Work week and holidays'), description: t('Working days and public holidays.') };
    case 'leave': return { title: t('Leave types'), description: t('The kinds of leave people can take.') };
    case 'team': return { title: t('Invite your team'), description: t('Add your employees and invite them to IceHrm.') };
    default: return { title: key, description: '' };
  }
}

export const DONE = 'Done';
export const SKIPPED = 'Skipped';
export const NOT_STARTED = 'NotStarted';
