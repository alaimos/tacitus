<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Utils;

use App\Models\User;
use Illuminate\Mail\Message;
use Mail;

class Mailer
{

    /**
     * Prepare email call parameters
     *
     * @param array         $options
     * @param array         $variables
     * @param callable|null $callback
     * @return array
     */
    protected static function prepareParameters(array $options, array $variables, callable $callback = null)
    {
        if (!isset($options['to'])) {
            throw new \RuntimeException('You must specify a recipient for this message');
        }
        if (!isset($options['subject'])) {
            throw new \RuntimeException('You must specify a message subject');
        }
        $to = $options['to'];
        if ($to instanceof User) {
            $toName = $to->name;
            $to = $to->email;
        } else {
            $toName = (isset($options['toName'])) ? $options['toName'] : null;
        }
        $variables['to'] = $to;
        $variables['toName'] = $toName;
        $subject = $options['subject'];
        $hasHtml = (isset($options['html']));
        $hasPlain = (isset($options['plain']));
        if (!$hasPlain && !$hasHtml) {
            throw new \RuntimeException('You must specify at least one kind of message (HTML or Plain Text)');
        }
        $hasHtml = !empty($htmlContent);
        $hasPlain = !empty($plainContent);
        $views = [];
        if ($hasHtml) {
            $views['html'] = $options['html'];
        }
        if ($hasPlain) {
            $views['text'] = $options['text'];
        }
        if (isset($options['variables']) && is_array($options['variables'])) {
            $variables = array_merge($variables, $options['variables']);
        }
        $callback = function (Message $message) use ($to, $toName, $subject, $callback) {
            $message->from(env('MAIL_FROM_ADDRESS', 'tacitus@local'), env('MAIL_FROM_NAME'))
                ->to($to, $toName)
                ->subject($subject);
            $replyTo = env('MAIL_REPLY_TO');
            if ($replyTo) {
                $message->replyTo($replyTo);
            }
            if ($callback !== null && is_callable($callback)) {
                call_user_func($callback, $message);
            }
        };
        return [$views, $variables, $callback];
    }

    /**
     * Send a mail message
     *
     * @param array         $options
     * @param callable|null $callback
     * @return mixed
     */
    public static function send(array $options, callable $callback = null)
    {
        $variables = []; //Not used right now
        list($view, $data, $callback) = self::prepareParameters($options, $variables, $callback);
        return Mail::send($view, $data, $callback);
    }

    /**
     * Enqueue a mail message
     *
     * @param array         $options
     * @param callable|null $callback
     * @return mixed
     */
    public static function enqueue(array $options, callable $callback = null)
    {
        $variables = []; //Not used right now
        list($view, $data, $callback) = self::prepareParameters($options, $variables, $callback);
        return Mail::queue($view, $data, $callback, 'mailer');
    }


}