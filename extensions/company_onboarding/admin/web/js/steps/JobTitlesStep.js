import React, { useEffect, useState } from 'react';
import { t } from '../../../../../../web/api/i18n';
import ListEditor from '../components/ListEditor';
import StepFooter from '../components/StepFooter';

// Titles suggested for departments whose name contains a keyword.
function titlesFor(department) {
  const d = department.toLowerCase();
  if (d.includes('human') || d === 'hr' || d.includes('people')) return [t('HR Manager'), t('HR Executive'), t('Recruiter')];
  if (d.includes('financ') || d.includes('account')) return [t('Finance Manager'), t('Accountant'), t('Accounts Assistant')];
  if (d.includes('sales')) return [t('Sales Manager'), t('Sales Executive')];
  if (d.includes('marketing')) return [t('Marketing Manager'), t('Marketing Executive')];
  if (d.includes('operation')) return [t('Operations Manager'), t('Operations Executive')];
  if (d.includes('engineer') || d.includes('develop') || d.includes('tech')) return [t('Engineering Manager'), t('Software Engineer'), t('QA Engineer')];
  if (d.includes('support') || d.includes('service')) return [t('Support Lead'), t('Support Agent')];
  if (d === 'it' || d.includes('information')) return [t('IT Manager'), t('IT Support')];
  if (d.includes('management') || d.includes('executive')) return [t('Managing Director'), t('General Manager')];
  return [];
}

/** Step 3 (optional): job titles, with suggestions for the departments created. */
export default function JobTitlesStep({
  api, step, busy, onContinue, onSkip,
}) {
  const [suggestions, setSuggestions] = useState([]);
  useEffect(() => {
    api.get('company-onboarding/structure').then((units) => {
      const list = [];
      units.filter((u) => u.type === 'Department' || u.type === 'Unit').forEach((u) => {
        titlesFor(u.title).forEach((title) => { if (!list.includes(title)) list.push(title); });
      });
      [t('Manager'), t('Team Lead'), t('Executive'), t('Assistant'), t('Intern')].forEach((title) => {
        if (!list.includes(title)) list.push(title);
      });
      setSuggestions(list);
    }).catch(() => {});
  }, []);

  return (
    <>
      <ListEditor api={api} listKey="job_titles" suggestions={suggestions} placeholder={t('Type a job title, or paste a list (one per line)')} />
      <StepFooter step={step} busy={busy} onSkip={onSkip} onContinue={onContinue} />
    </>
  );
}
