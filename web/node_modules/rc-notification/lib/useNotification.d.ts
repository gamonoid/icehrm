import * as React from 'react';
import Notification, { NoticeFunc } from './Notification';
export default function useNotification(notificationInstance: Notification): [NoticeFunc, React.ReactElement];
