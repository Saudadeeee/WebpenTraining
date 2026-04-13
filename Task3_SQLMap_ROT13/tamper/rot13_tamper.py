

from lib.core.enums import PRIORITY
import codecs

__priority__ = PRIORITY.NORMAL


def dependencies():
    pass


def tamper(payload, **kwargs):
    return codecs.encode(payload, "rot_13") if payload else payload
