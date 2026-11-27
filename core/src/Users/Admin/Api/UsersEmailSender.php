<?php
namespace Users\Admin\Api;

use Classes\Email\EmailParts;
use Classes\Email\EmailSender;
use Model\UserInvitation;
use Users\Common\Model\User;
use Classes\SubActionManager;
use Utils\LogManager;

class UsersEmailSender
{

    protected $emailSender = null;
    protected $subActionManager = null;

    public function __construct(EmailSender $emailSender, SubActionManager $subActionManager)
    {
        $this->emailSender = $emailSender;
        $this->subActionManager = $subActionManager;
    }

    /** "Your account is ready" email with a temporary password, sent when an admin creates a user. */
    public function sendWelcomeUserEmail(User $user, $password, $employee = null)
    {
        if (empty($user) || empty($user->email)) {
            LogManager::getInstance()->info("[sendWelcomeUserEmail] email is empty");
            return false;
        }
        if (empty($this->emailSender)) {
            return false;
        }

        $company = EmailParts::companyName();
        $firstName = !empty($employee) && !empty($employee->first_name) ? $employee->first_name : $user->username;
        $url = CLIENT_BASE_URL;
        $intro = sprintf('An account has been created for you at %s. Use the details below to sign in.', $company);

        $body = EmailParts::greeting($firstName)
            . EmailParts::text($intro)
            . EmailParts::details(array(
                'Username' => $user->username,
                'Email' => $user->email,
                'Temporary password' => $password,
            ))
            . EmailParts::small('You can sign in with either your username or your email address.')
            . EmailParts::button($url, 'Sign in')
            . EmailParts::fallbackLink($url)
            . EmailParts::small('For your security, change this temporary password after you sign in for the first time.')
            . EmailParts::signOff();

        LogManager::getInstance()->info("[sendWelcomeUserEmail] sending welcome email to ".$user->email);
        return $this->emailSender->sendEmail(
            sprintf('Your %s account is ready', $company),
            $user->email,
            $body,
            array(
                'url' => $url,
                'preheader' => EmailParts::esc(sprintf('Your %s account is ready. Here is how to sign in.', $company)),
            )
        );
    }

    /** Invitation to set up an account (choose a password) through a one-time link. */
    public function sendInviteUserEmail(UserInvitation $userInvitation, $inviter, $token = null)
    {
        if (empty($userInvitation->email)) {
            LogManager::getInstance()->info("[sendInviteUserEmail] email is empty");
            return false;
        }
        if (empty($this->emailSender)) {
            return false;
        }

        $company = EmailParts::companyName();
        $inviterName = '';
        if (!empty($inviter)) {
            $inviterName = trim($inviter->first_name . ' ' . $inviter->last_name);
        }

        $acceptUrl = \Employees\Services\UserInvitationService::getAcceptUrl((string) $token);

        if ($inviterName !== '') {
            $subject = sprintf('%s invited you to join %s', $inviterName, $company);
            $intro = sprintf('%s has invited you to join %s. Choose a password to set up your account.', $inviterName, $company);
            $reason = sprintf('You received this email because %s invited you to %s.', $inviterName, $company);
        } else {
            $subject = sprintf('You are invited to join %s', $company);
            $intro = sprintf('You have been invited to join %s. Choose a password to set up your account.', $company);
            $reason = sprintf('You received this email because %s invited you to join.', $company);
        }

        // Extensions (e.g. onboarding) may add a block such as the start date and a
        // welcome message. Providers return HTML built with EmailParts (escaped).
        $extras = (new \Employees\Services\UserInvitationService())->getAcceptPageExtras($userInvitation);
        $extra = isset($extras['email_html']) ? $extras['email_html'] : '';

        $expiry = 'This link can be used once';
        if (!empty($userInvitation->expires_at)) {
            $expiry .= ' and expires on ' . EmailParts::date($userInvitation->expires_at);
        }
        $expiry .= '. If it has expired, ask your HR team to send a new invitation.';

        $body = EmailParts::greeting($userInvitation->first_name)
            . EmailParts::text($intro)
            . $extra
            . EmailParts::button($acceptUrl, 'Set up your account')
            . EmailParts::fallbackLink($acceptUrl)
            . EmailParts::small($expiry)
            . EmailParts::signOff();

        LogManager::getInstance()->info("[sendInviteUserEmail] sending invitation email to ".$userInvitation->email);
        return $this->emailSender->sendEmail(
            $subject,
            $userInvitation->email,
            $body,
            array(
                'url' => CLIENT_BASE_URL,
                'preheader' => EmailParts::esc($intro),
                'emailReason' => EmailParts::esc($reason),
            )
        );
    }
}
